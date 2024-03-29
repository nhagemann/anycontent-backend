<?php

namespace AnyContent\Backend\Modules\Exchange;

use AnyContent\Client\Record;
use AnyContent\Client\Repository;
use CMDL\Util;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Importer
{
    protected $generateNewIDs = false;

    protected $truncateRecords = false;

    protected $newerRevisionUpdateProtection = false;

    protected $propertyChangesCheck = false;

    protected $count = 0;

    protected $output;

    protected $error = false;

    protected ?array $records = null;

    /**
     * @var array stashed records to be saved
     */
    protected $stash = [];

    public function importJSON(Repository $repository, $contentTypeName, $data, $workspace = 'default', $language = 'default', $viewName = 'exchange')
    {
        $this->reset();

        $repository->selectContentType($contentTypeName);

        // Select view and fallback if necessary
        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $viewDefinition = $contentTypeDefinition->getExchangeViewDefinition($viewName);
        $viewName = $viewDefinition->getName();

        $repository->selectWorkspace($workspace);
        $repository->selectLanguage($language);
        $repository->selectView($viewName);

        $data = json_decode($data, true);

        if (json_last_error() != 0) {
            $this->writeln('Error parsing JSON data.');
            $this->error = true;

            return -1;
        }

        if (array_key_exists('records', $data)) {
            if ($this->isTruncateRecords()) {
                $this->deleteEffectiveRecords($repository);
            }

            $rows = $data['records'];

            foreach ($rows as $row) {
                $id = $row['id'];
                $properties = $row['properties'];

                if ($this->isGenerateNewIDs()) {
                    $id = null;
                }

                $record = new Record($contentTypeDefinition, 'Imported Record', $viewName, $workspace, $language);
                $record->setProperties($properties);
                $record->setID($id);

                $msg = $this->stashRecord($repository, $record);

                $this->writeln($msg);
            }

            $this->writeln('');
            $this->writeln('Found ' . $this->count . ' records to import');
            $this->writeln('');

            if ($this->count > 0) {
                $this->writeln('Starting bulk import');
                $this->writeln('');
                $this->saveRecords($repository);
                $this->writeln('');
                $this->writeln('');
            }
        }

        return !$this->error;
    }

    public function importXLSX(Repository $repository, $contentTypeName, $filename, $workspace = 'default', $language = 'default', $viewName = 'exchange')
    {
        $this->reset();

        $repository->selectContentType($contentTypeName);

        // Select view and fallback if necessary
        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $viewDefinition = $contentTypeDefinition->getExchangeViewDefinition($viewName);
        $viewName = $viewDefinition->getName();

        $repository->selectWorkspace($workspace);
        $repository->selectLanguage($language);
        $repository->selectView($viewName);

        try {
            $objPHPExcel = IOFactory::load($filename);
            $objWorksheet = $objPHPExcel->getActiveSheet();

            $this->importExcelSheet($repository, $objWorksheet);
        } catch (\Exception $exception) {
            $this->writeln('Error parsing Excel file.');
            $this->error = true;
        }

        return !$this->error;
    }

    protected function importExcelSheet(Repository $repository, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $objWorksheet)
    {
        if ($this->isTruncateRecords()) {
            $this->deleteEffectiveRecords($repository);
        }

        $contentTypeDefinition = $repository->getCurrentContentTypeDefinition();

        $viewName = $repository->getCurrentDataDimensions()->getViewName();
        $workspace = $repository->getCurrentDataDimensions()->getWorkspace();
        $language = $repository->getCurrentDataDimensions()->getLanguage();

        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
        $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
        $idColumnIndex = null;

        $propertiesColumnIndices = [];

        for ($i = 0; $i <= $highestColumnIndex; $i++) {
            $value = trim($objWorksheet->getCellByColumnAndRow($i + 1, 1)->getValue());
            if ($value != '') {
                if (substr($value, 0, 1) == '.') {
                    if ($value == '.id') {
                        $idColumnIndex = $i;
                    }
                } else {
                    $value = Util::generateValidIdentifier($value);
                    if ($contentTypeDefinition->hasProperty($value, $viewName)) {
                        $this->writeln('Detected valid property ' . $value);
                        $propertiesColumnIndices[$value] = $i;
                    }
                }
            }
        }

        $this->writeln('');

        if (count($propertiesColumnIndices) != 0) {
            for ($row = 2; $row <= $highestRow; ++$row) {
                $id = null;
                if ($idColumnIndex !== null) {
                    if (!$this->isGenerateNewIDs()) {
                        $id = $objWorksheet->getCellByColumnAndRow($idColumnIndex + 1, $row)->getValue();
                    }
                }
                $properties = [];
                foreach ($propertiesColumnIndices as $property => $col) {
                    $value = $objWorksheet->getCellByColumnAndRow($col + 1, $row)->getValue();
                    $properties[$property] = $value;
                }

                $record = new Record($contentTypeDefinition, 'Imported Record', $viewName, $workspace, $language);
                $record->setProperties($properties);
                $record->setId($id);

                $msg = $this->stashRecord($repository, $record);

                $this->writeln($msg);
            }

            $this->writeln('');
            $this->writeln('Found ' . $this->count . ' records to import');
            $this->writeln('');

            if ($this->count != 0) {
                $this->writeln('Starting bulk import');
                $this->writeln('');
                $this->saveRecords($repository);
                $this->writeln('');
                $this->writeln('');
            }
        } else {
            $this->writeln('Excel sheet does not contain matching property columns.');
        }
    }

    protected function stashRecord(Repository $repository, Record $record)
    {
        $msg = trim('Preparing record ' . $record->getId()) . ' - ' . $record->getName();

        if ($this->isNewerRevisionUpdateProtection()) {
            if ($this->gotNewerRevision($repository, $record)) {
                return 'Skipping record ' . $record->getId() . ' - ' . $record->getName() . (' (Newer Revision)');
            }
        }

        if ($this->isPropertyChangesCheck() == false || $this->hasChanged($repository, $record)) {
            $this->stash[] = $record;
            $this->count++;
        } else {
            $msg = 'Skipping record ' . $record->getId() . ' - ' . $record->getName() . (' (No changes)');
        }

        return $msg;
    }

    protected function saveRecords(Repository $repository)
    {
        $result = $repository->saveRecords($this->stash);

        if ($result) {
            foreach ($result as $v) {
                $this->writeln('Imported record. Id ' . $v . ' has been asigned.');
            }
        }
    }

    protected function hasChanged(Repository $repository, Record $record)
    {
        if ($record->getId() != null) {
            $records = $this->getRecords($repository);

            if (isset($records[$record->getId()])) {
                /** @var Record $effectiveRecord */
                $effectiveRecord = $records[$record->getId()];
                foreach ($record->getProperties() as $property => $value) {
                    if ($effectiveRecord->getProperty($property) != $value) {
                        return true;
                    }
                }

                return false;
            }
        }

        return true;
    }

    protected function gotNewerRevision(Repository $repository, Record $record)
    {
        if ($record->getId() != null && $record->getRevision() != null) {
            $records = $this->getRecords($repository);

            if (isset($records[$record->getId()])) {
                /** @var Record $effectiveRecord */
                $effectiveRecord = $records[$record->getId()];

                if ($effectiveRecord->getRevision() > $record->getRevision()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function deleteEffectiveRecords(Repository $repository)
    {
        $this->writeln('');
        $this->writeln('Deleting all records in workspace ' . $repository->getCurrentDataDimensions()
                ->getWorkspace() . ' with language ' . $repository->getCurrentDataDimensions()
                ->getLanguage());

        $repository->deleteAllRecords();

        $this->records = null;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    protected function getRecords(Repository $repository)
    {
        if (!$this->records) {
            $this->writeln('');
            $this->writeln('Start fetching current effective records');
            $this->writeln('');
            $this->records = $repository->getRecords();
            $this->writeln('Done fetching current effective records');
            $this->writeln('');
        }

        return $this->records;
    }

    /**
     * @return boolean
     */
    public function isGenerateNewIDs()
    {
        return $this->generateNewIDs;
    }

    /**
     * @param boolean $generateNewIDs
     */
    public function setGenerateNewIDs($generateNewIDs)
    {
        $this->generateNewIDs = $generateNewIDs;
    }

    /**
     * @return boolean
     */
    public function isNewerRevisionUpdateProtection()
    {
        return $this->newerRevisionUpdateProtection;
    }

    /**
     * @param boolean $newerRevisionUpdateProtection
     */
    public function setNewerRevisionUpdateProtection($newerRevisionUpdateProtection)
    {
        $this->newerRevisionUpdateProtection = $newerRevisionUpdateProtection;
    }

    /**
     * @return boolean
     */
    public function isTruncateRecords()
    {
        return $this->truncateRecords;
    }

    /**
     * @param boolean $truncateRecords
     */
    public function setTruncateRecords($truncateRecords)
    {
        $this->truncateRecords = $truncateRecords;
    }

    /**
     * @return boolean
     */
    public function isPropertyChangesCheck()
    {
        return $this->propertyChangesCheck;
    }

    /**
     * @param boolean $propertyChangesCheck
     */
    public function setPropertyChangesCheck($propertyChangesCheck)
    {
        $this->propertyChangesCheck = $propertyChangesCheck;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    protected function writeln($msg)
    {
        if ($this->output) {
            $this->output->writeln($msg);
        }
    }

    public function reset(): void
    {
        $this->count = 0;
        $this->records = null;
        $this->stash = [];
        $this->error = false;
    }
}
