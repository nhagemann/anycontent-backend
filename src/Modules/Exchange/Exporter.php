<?php

namespace AnyContent\Backend\Modules\Exchange;

use AnyContent\Client\Record;
use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Exporter
{
    protected $output;

    protected $errors = [];

    public function exportJSON(Repository $repository, $contentTypeName, $workspace = 'default', $language = 'default', $viewName = 'exchange')
    {
        $repository->selectContentType($contentTypeName);

        // Select view and fallback if necessary
        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $viewDefinition        = $contentTypeDefinition->getExchangeViewDefinition($viewName);
        $viewName              = $viewDefinition->getName();

        $this->writeln('Connecting repository');
        $this->writeln('');

        $repository->selectWorkspace($workspace);
        $repository->selectLanguage($language);
        $repository->selectView($viewName);

        /** @var Record[] $records */
        $records = $repository->getRecords('', 'id');

        if ($records !== false) {
            $result                         = [];
            $result['info']['content_type'] = $contentTypeName;
            $result['info']['workspace']    = $workspace;
            $result['info']['view']         = $viewName;
            $result['info']['language']     = $language;
            $result['info']['count']        = (string)count($records);

            $result['records'] = [];

            foreach ($records as $record) {
                $this->writeln('Processing record ' . $record->getID() . ' - ' . $record->getName());

                $result['records'][$record->getID()] = ['id' => $record->getID(), 'revision' => $record->getRevision(), 'properties' => $record->getProperties()];
            }

            return json_encode($result, JSON_PRETTY_PRINT);
        }

        return false;
    }

    public function exportXLSX(Repository $repository, $contentTypeName, $workspace = 'default', $language = 'default', $viewName = 'exchange')
    {
        $this->errors = [];
        $repository->selectContentType($contentTypeName);

        // Select view and fallback if necessary
        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $viewDefinition        = $contentTypeDefinition->getExchangeViewDefinition($viewName);
        $viewName              = $viewDefinition->getName();

        $this->writeln('Connecting repository');
        $this->writeln('');

        $repository->selectWorkspace($workspace);
        $repository->selectLanguage($language);
        $repository->selectView($viewName);

        /** @var Record[] $records */
        $records = $repository->getRecords('', '.id', 1);

        if ($records !== false) {
            $objPHPExcel = $this->createExcelDocument('Content Export for content type ' . $contentTypeDefinition->getTitle());

            $objPHPExcel = $this->addRecordsToExcelSheet($objPHPExcel, 0, $records, $contentTypeDefinition, $viewName, 'Export');

            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, IOFactory::WRITER_XLSX);
            ob_start();
            $objWriter->save('php://output');
            return ob_get_clean();
        }

        return false;
    }

    public function backupXLSX(Repository $repository, $contentTypeName = null, $viewName = 'exchange')
    {
        $objPHPExcel = $this->createExcelDocument('Content Export for repository ' . $repository->getName());

        if ($contentTypeName != null) {
            $contentTypeNames = [$contentTypeName];
        } else {
            $contentTypeNames = $repository->getContentTypeNames();
        }

        $i = 0;
        foreach ($contentTypeNames as $contentTypeName) {
            $repository->selectContentType($contentTypeName);

            // Select view and fallback if necessary
            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $viewDefinition        = $contentTypeDefinition->getExchangeViewDefinition($viewName);
            $viewName              = $viewDefinition->getName();

            $repository->selectView($viewName);

            foreach ($contentTypeDefinition->getWorkspaces() as $workspace => $workspaceName) {
                $repository->selectWorkspace($workspace);
                foreach ($contentTypeDefinition->getLanguages() as $language => $languageName) {
                    $repository->selectLanguage($language);

                    $title = $contentTypeName . '.' . $workspace . '.' . $language;

                    /** @var Record[] $records */
                    $records = $repository->getRecords('', '.id', 1);

                    $this->writeln('Writing sheet ' . $title . ' with ' . count($records) . ' record(s).');

                    $objPHPExcel = $this->addRecordsToExcelSheet($objPHPExcel, $i, $records, $contentTypeDefinition, $viewName, $title);

                    $i++;
                }
            }
        }

        $objPHPExcel->setActiveSheetIndex();

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, IOFactory::WRITER_XLSX);
        ob_start();
        $objWriter->save('php://output');
        return ob_get_clean();
    }

    protected function createExcelDocument($title)
    {
        // Create new PHPExcel object
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // use temp folder for processing of large files
        //$cacheMethod   = \PhpOffice\PhpSpreadsheet\Collection\CellsFactory::cache_to_phpTemp;
        //$cacheSettings = array('memoryCacheSize' => '12MB');
        //\PhpOffice\PhpSpreadsheet\Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("AnyContent CMCK")
                    ->setLastModifiedBy("AnyContent CMCK")
                    ->setTitle($title)
                    ->setSubject("AnyContent Export")
                    ->setDescription("");

        return $objPHPExcel;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel
     * @param                       $i
     * @param Record[]              $records
     * @param ContentTypeDefinition $contentTypeDefinition
     * @param                       $viewName
     * @param                       $title
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function addRecordsToExcelSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel, $i, $records, ContentTypeDefinition $contentTypeDefinition, $viewName, $title)
    {
        if ($i > 0) {
            $objPHPExcel->createSheet($i);
        }
        $worksheet = $objPHPExcel->setActiveSheetIndex($i);

        if (strlen($title) > 31) {
            $worksheet->setTitle('export.sheet.' . $i);
        } else {
            $worksheet->setTitle($title);
        }

        //$worksheet->getComment()->getText()->createTextRun($title);

        $worksheet->setCellValueByColumnAndRow(1, 1, '.id');
        $worksheet->getStyleByColumnAndRow(1, 1)->getFont()->setBold(false)->setItalic(true);

        $worksheet->setCellValueByColumnAndRow(2, 1, '.revision');
        $worksheet->getStyleByColumnAndRow(2, 1)->getFont()->setBold(false)->setItalic(true);

        $row    = 1;
        $column = 3;
        foreach ($contentTypeDefinition->getProperties($viewName) as $property) {
            $worksheet->setCellValueByColumnAndRow($column, $row, $property);
            $worksheet->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
            $worksheet->getColumnDimensionByColumn($column)->setWidth(20);
            $column++;
        }

        $row++;

        /** @var Record $record */
        foreach ($records as $record) {
            $this->writeln('Processing record ' . $record->getId() . ' - ' . $record->getName());

            $worksheet->setCellValueByColumnAndRow(1, $row, $record->getId());
            $worksheet->setCellValueByColumnAndRow(2, $row, $record->getRevision());

            $column = 3;
            foreach ($contentTypeDefinition->getProperties($viewName) as $property) {
                $value = $record->getProperty($property);
                if (strlen($value) > 32767) {
                    $this->addError('The Excel character limit for a cell has been exceeded. Could not export record ' . $record->getId() . ' successfully. File corrupt.');
                }
                $worksheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }
            $row++;
        }

        return $objPHPExcel;
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

    public function gotErrors()
    {
        return (bool)count($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $error
     */
    protected function addError($error)
    {
        $this->errors[] = $error;
    }
}
