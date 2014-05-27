<#1>
<?php
    require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Reporting/classes/class.ilReportingPlugin.php');
    $pl = new ilReportingPlugin();
    $conf = $pl->getConfigObject();
    $conf->initDB();
    $conf->setValue('header_image', ilReportingPlugin::getRootDir() . 'jasper_report/logo.jpg');
?>
<#2>
<?php
$pl = new ilReportingPlugin();
?>
<#3>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Reporting/classes/class.ilReportingPlugin.php');
$pl = new ilReportingPlugin();
$conf = $pl->getConfigObject();
$conf->initDB();
if ($conf->getValue('jasper_reports_templates_path') === false) {
    $conf->setValue('jasper_reports_templates_path', ilReportingPlugin::getRootDir() . 'jasper_report/');
}
?>