<?php
require_once('./Services/Excel/classes/class.ilExcelUtils.php');
require_once('./Services/Excel/classes/class.ilExcelWriterAdapter.php');
require_once('class.ilReportingPlugin.php');

/**
 * Class ilReportingExcelExport. Base class for each report that enables to generate a custom excel export.
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
abstract class ilReportingExcelExport {

    const COLOR_BLUE = 44;
    const DATE_FORMAT = 'd.M Y, H:i';
    const EXCEL_FILE_TYPE = 'xls';

    /**
     * @var ilExcelWriterWrapper|null
     */
    protected $workbook;

    protected $h1;
    protected $h3;

    /**
     * @var ilReportingPlugin
     */
    protected $pl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var array
     */
    protected $column_lengths = array();

    /**
     * @param string $filename Excel filename
     */
    public function __construct($filename = '') {
        global $ilUser, $lng;
        if (!$filename) $filename = 'export_' . date('y_m_d') . '.' . self::EXCEL_FILE_TYPE;
        if (!preg_match('#.+\.xlsx?$#', $filename)) {
            $filename .= '.' . self::EXCEL_FILE_TYPE;
        }
        $adapter = new ilExcelWriterAdapter($filename, true);
        $this->workbook = $adapter->getWorkbook();
        $this->h1 = $this->workbook->addFormat(array('Size' => 26, 'Bold' => true));
        $this->h3 = $this->workbook->addFormat(array('Bold' => true, 'FgColor' => self::COLOR_BLUE));
        $this->workbook->df = "d.m.Y";
        $this->pl = new ilReportingPlugin();
        $this->user = $ilUser;
        $this->lng = $lng;
    }

    /**
     * Generate export
     * @param array $data
     */
    public abstract function execute(array $data);


    /**
     * Set width of columns of a given worksheet according to the stored string lengths
     * Resets the stored lengths in case other worksheets are processed
     *
     * @param $worksheet
     */
    public function autoSizeColumns($worksheet) {
        foreach ($this->column_lengths as $k => $length) {
            $worksheet->setColumn($k, $k, $this->strLength2Width($length));
        }
        $this->column_lengths = array();
    }


    /**
     * Wrapper method to write a string in a worksheet.
     * This method stores the longest string per column. This allows to set the width of columns according
     * to their string length (simulate auto Excels auto-width).
     *
     * @param $worksheet
     * @param $row
     * @param $col
     * @param $string
     * @param $format
     */
    protected function write($worksheet, $row, $col, $string, $format='') {
        $worksheet->write($row, $col, $string, $format);
        if ($row == 0) {
            // Skip headline...
            return;
        }
        $length = mb_strlen($string);
        if (isset($this->column_lengths[$col])) {
            $given_length = $this->column_lengths[$col];
            if ($length > $given_length) {
                $this->column_lengths[$col] = $length;
            }
        } else {
            $this->column_lengths[$col] = $length;
        }
    }


    /**
     * Map a string length to a column width
     *
     * @param int $length
     * @return int
     */
    protected function strLength2Width($length) {
        return $length * 0.9;
    }

    protected function formatData($data) {
        $tmp = array();
        foreach ($data as $v) {
            $tmp[$v['id']][] = $v;
        }
        return $tmp;
    }

}