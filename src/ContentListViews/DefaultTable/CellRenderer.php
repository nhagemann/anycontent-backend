<?php

namespace AnyContent\Backend\ContentListViews\DefaultTable;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class CellRenderer
{
    public function __construct(
        private ContextManager $contextManager,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    public function getUrlGenerator()
    {
        return $this->urlGenerator;
    }

    /**
     * @param BaseColumn $column
     * @param Record     $record
     *
     * @return string
     */
    public function render(BaseColumn $column, Record $record)
    {
        $template = '@AnyContentBackend/Listing/listing-cell.html.twig';

        $vars                 = [];
        $vars['value']        = $column->getValue($record);
        $vars['link']         = false;
        $vars['badge']        = $column->isBadge();
        $vars['badgeclass']   = 'badge';
        $vars['editButton']   = false;
        $vars['deleteButton'] = false;
        $vars['customButton'] = false;

        if ($column->getType() == 'Button') {
            if ($column->isEditButton()) {
                $vars['editButton'] = true;
                $vars['editLink']   = $this->getEditLink($record);
            }
            if ($column->isDeleteButton()) {
                $vars['deleteButton'] = true;
                $vars['deleteLink']   = $this->getDeleteLink($record);
            }
        }

        if ($column->getType() == 'Attribute') {
            switch ($column->getAttribute()) {
                case 'id':
                    $vars['value'] = $record->getID();
                    break;
                case 'revision':
                    $vars['value'] = $record->getRevision();
                    break;
                case 'position':
                    $vars['value'] = $record->getPosition();
                    break;
                case 'parent_id':
                    $vars['value'] = $record->getParentRecordId();
                    break;
                case 'level':
                    $vars['value'] = $record->getLevelWithinSortedTree();
                    break;
                case 'lastchange':
                    $template = '@AnyContentBackend/Listing/listing-cell-userinfo.html.twig';
                    $vars     = $this->getUserInfoVars($record->getLastChangeUserInfo());
                    break;
                case 'creation':
                    $template = '@AnyContentBackend/Listing/listing-cell-userinfo.html.twig';
                    $vars     = $this->getUserInfoVars($record->getCreationUserInfo());
                    break;
            }
        }

        if ($column instanceof SubtypeColumn) {
            $vars['badgeclass'] = 'badge subtype subtype-' . strtolower($record->getSubtype());
        }

        if ($column instanceof StatusColumn) {
            $vars['badgeclass'] = 'badge status status-' . strtolower($record->getSubtype());
        }

        if ($column->isLinkToRecord()) {
            $vars['link'] = $this->getEditLink($record);
        }

        return $this->getTwig()->render($template, $vars);
    }

    protected function getUserInfoVars(UserInfo $userInfo)
    {
        $vars = [];
        $vars['username'] = $userInfo->getName();
        $date             = new \DateTime();
        $date->setTimestamp($userInfo->getTimestamp());
        $vars['date']     = $date->format('d.m.Y H:i:s');
        $vars['gravatar'] = md5(trim($userInfo->getUsername()));

        return $vars;
    }

    protected function getEditLink(Record $record)
    {
        return $this->getUrlGenerator()->generate('anycontent_record_edit', [ 'contentTypeAccessHash' => $this->contextManager
                                                                                                       ->getCurrentContentTypeAccessHash(),
                                                                       'recordId' => $record->getID(), 'workspace' => $this->contextManager
                                                                                                                           ->getCurrentWorkspace(), 'language' => $this->contextManager
                                                                                                                                                                       ->getCurrentLanguage(),
        ]);
    }

    protected function getDeleteLink(Record $record)
    {
        return $this->getUrlGenerator()->generate(
            'anycontent_record_delete',
            [ 'contentTypeAccessHash' => $this->contextManager
                                                                                         ->getCurrentContentTypeAccessHash(), 'recordId' => $record->getID(), 'workspace' => $this->contextManager
                                                                                                                                                                                  ->getCurrentWorkspace(), 'language' => $this->contextManager
                                                                                                                                                                                                                              ->getCurrentLanguage(),
            ]
        );
    }
}
