<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;
use AnyContent\CMCK\Modules\Backend\Core\Context\ContextManager;
use CMDL\FormElementDefinition;
use Silex\Application;
use Symfony\Component\Routing\Generator\UrlGenerator;

class CellRenderer
{

    /** @var  Application */
    protected $app;


    public function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->app['twig'];
    }


    /**
     * @return UrlGenerator
     */
    public function getUrlGenerator()
    {
        return $this->app['url_generator'];
    }


    /**
     * @return ContextManager
     */
    public function getContext()
    {
        return $this->app['context'];
    }


    /**
     * @param BaseColumn $column
     * @param Record     $record
     *
     * @return string
     */
    public function render(BaseColumn $column, Record $record)
    {
        $template = 'listing-cell.twig';

        $vars                 = array();
        $vars['value']        = $column->getValue($record);
        $vars['link']         = false;
        $vars['badge']        = $column->isBadge();
        $vars['badgeclass']   = 'badge';
        $vars['editButton']   = false;
        $vars['deleteButton'] = false;
        $vars['customButton'] = false;

        if ($column->getType() == 'Button')
        {
            if ($column->isEditButton())
            {
                $vars['editButton'] = true;
                $vars['editLink']   = $this->getEditLink($record);
            }
            if ($column->isDeleteButton())
            {
                $vars['deleteButton'] = true;
                $vars['deleteLink']   = $this->getDeleteLink($record);
            }
        }

        if ($column->getType() == 'Attribute')
        {
            switch ($column->getAttribute())
            {
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
                    $template = 'listing-cell-userinfo.twig';
                    $vars     = $this->getUserInfoVars($record->getLastChangeUserInfo());
                    break;
                case 'creation':
                    $template = 'listing-cell-userinfo.twig';
                    $vars     = $this->getUserInfoVars($record->getCreationUserInfo());
                    break;
            }
        }

        if ($column instanceof SubtypeColumn)
        {
            $vars['badgeclass']='badge subtype subtype-'.strtolower($record->getSubtype());
        }

        if ($column instanceof StatusColumn)
        {
            $vars['badgeclass']='badge status status-'.strtolower($record->getSubtype());
        }

        if ($column->isLinkToRecord())
        {
            $vars['link'] = $this->getEditLink($record);
        }

        return $this->getTwig()->render($template, $vars);
    }


    protected function getUserInfoVars(UserInfo $userInfo)
    {
        $vars['username'] = $userInfo->getName();
        $date             = new \DateTime();
        $date->setTimestamp($userInfo->getTimestamp());
        $vars['date']     = $date->format('d.m.Y H:i:s');
        $vars['gravatar'] = md5(trim($userInfo->getUsername()));

        return $vars;
    }


    protected function getEditLink(Record $record)
    {
        return $this->getUrlGenerator()->generate('editRecord', array( 'contentTypeAccessHash' => $this->getContext()
                                                                                                       ->getCurrentContentTypeAccessHash(),
                                                                       'recordId' => $record->getID(), 'workspace' => $this->getContext()
                                                                                                                           ->getCurrentWorkspace(), 'language' => $this->getContext()
                                                                                                                                                                       ->getCurrentLanguage()
        ));

    }


    protected function getDeleteLink(Record $record)
    {
        return $this->getUrlGenerator()->generate('deleteRecord',
                                                  array( 'contentTypeAccessHash' => $this->getContext()
                                                                                         ->getCurrentContentTypeAccessHash(), 'recordId' => $record->getID(), 'workspace' => $this->getContext()
                                                                                                                                                                                  ->getCurrentWorkspace(), 'language' => $this->getContext()
                                                                                                                                                                                                                              ->getCurrentLanguage()
                                                  ));

    }
}

