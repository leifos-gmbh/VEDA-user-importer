<?php

class ilVedaCategoryRefIdNumberInput extends ilNumberInputGUI
{
    protected ilPlugin $plugin;

    public function __construct(ilPlugin $plugin, string $a_title = "", string $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->plugin = $plugin;
    }

    public function checkInput(): bool
    {
        $parent_input_check = parent::checkInput();
        if (
            $parent_input_check &&
            !$this->inputIsCategoryRefId()
        ) {
            $this->setAlert($this->plugin->txt('tbl_settings_course_import_category_error'));
            return false;
        }
        return $parent_input_check;
    }

    protected function inputIsCategoryRefId(): bool
    {
        return ilObject::_lookupType((int) $this->getInput(), true) === 'cat';
    }
}