<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Table GUI for course import results
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaCourseImportResultTableGUI extends \ilTable2GUI
{
    protected ilVedaConnectorPlugin $plugin;
    protected ilTree $repository_tree;
    
    public function __construct($class, string $method)
    {
        global $DIC;
        $this->repository_tree = $DIC->repositoryTree();
        $this->plugin = ilVedaConnectorPlugin::getInstance();
        $this->setId('vedaimp_res_crs');
        parent::__construct($class, $method);
    }
    
    /**
     * init table
     */
    public function init()
    {
        $this->setTitle($this->plugin->txt('tbl_import_result_crs'));
        $this->setFormAction($this->getFormAction());

        $this->setRowTemplate(
            'tpl.crs_result_row.html',
            $this->plugin->getDirectory()
        );

        $this->addColumn(
            $this->lng->txt('title'),
            'title'
        );
        $this->addColumn(
            $this->lng->txt('type'),
            'type'
        );
        $this->addColumn(
            $this->plugin->txt('tbl_crs_result_created'),
            'created'
        );
        $this->addColumn(
            $this->plugin->txt('tbl_crs_result_pswitch'),
            'pswitch'
        );
        $this->addColumn(
            $this->plugin->txt('tbl_crs_result_tswitch'),
            'tswitch'
        );
    }

    /**
     * Parse imported user data
     * @throws \ilDatabaseException
     */
    public function parse()
    {
        $courses = (new ilVedaRepositoryFactory())->getCourseRepository()->lookupAllCourses();
        $rows = [];
        foreach ($courses as $course) {
            $rows[] = [
                'obj_id' => $course->getObjId(),
                'title' => \ilObject::_lookupTitle($course->getObjId()),
                'type' => $course->getType(),
                'oid' => $course->getOid(),
                'created' => $course->getCreationStatus(),
                'pswitch' => $course->getPermanentSwitchRole(),
                'tswitch' => $course->getTemporarySwitchRole()
            ];
        }
        $this->setData($rows);
    }

    protected function fillRow($a_set)
    {
        $obj_id = $a_set['obj_id'];
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref = end($refs);
        if (!$ref || $this->repository_tree->isDeleted($ref)) {
            $this->tpl->setCurrentBlock('is_deleted');
            $this->tpl->setVariable('TXT_DELETED', $this->lng->txt('deleted'));
        } else {
            $link = \ilLink::_getLink($ref);
            $this->tpl->setCurrentBlock('with_title');
            $this->tpl->setVariable('TITLE_LINK', $link);
            $this->tpl->setVariable('TXT_TITLE', \ilObject::_lookupTitle($obj_id));
        }
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable(
            'TXT_TYPE',
            $a_set['type'] ==
            ilVedaCourseType::SIFA ?
                $this->plugin->txt('type_sifa') :
                $this->plugin->txt('type_standard')
        );
        $this->tpl->setVariable(
            'CREATED_IMG',
            $a_set['created'] == ilVedaCourseStatus::SYNCHRONIZED ?
                ilUtil::getImagePath('icon_ok.svg') :
                ilUtil::getImagePath('icon_not_ok.svg')
        );

        if (\ilObject::_exists($a_set['tswitch'])) {
            $this->tpl->setVariable('TXT_TAVAILABLE', $this->plugin->txt('role_available'));
        } else {
            $this->tpl->setVariable('TXT_TAVAILABLE', $this->plugin->txt('role_unavailable'));
        }
        if (\ilObject::_exists($a_set['pswitch'])) {
            $this->tpl->setVariable('TXT_PAVAILABLE', $this->plugin->txt('role_available'));
        } else {
            $this->tpl->setVariable('TXT_PAVAILABLE', $this->plugin->txt('role_unavailable'));
        }
        $this->tpl->setVariable('OID', $a_set['oid']);
    }
}
