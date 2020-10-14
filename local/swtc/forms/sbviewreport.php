<?php
       // sbviewreport.php - shows the ServiceBench report that is passed in.

    require_once("../config.php");
    require_once($CFG->libdir.'/adminlib.php');

    admin_externalpage_setup('sbviewreport');
    
    // Get the report to view.
    // $report = required_param('report', PARAM_STRING);
    
    // print_object($report);

    echo $OUTPUT->header();

    

    echo $OUTPUT->footer();


