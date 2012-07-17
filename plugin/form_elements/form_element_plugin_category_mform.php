<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist_mform.class.php');

class form_element_plugin_category_mform  extends form_element_plugin_itemlist_mform {

	function __construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$formfield_id=null)    {
        parent::__construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$formfield_id=null);
        //remember to define $this->tablename and $this->items_tablename in the child class
        $this->tablename = 'ulcc_form_plg_cat';
        $this->items_tablename = 'ulcc_form_plg_cat_items';
	}
}
