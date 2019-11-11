<?php
namespace app\widgets\Export;


use backend\controllers\ReportsController;
use kartik\export\ExportMenu;
use yii\base\ActionEvent;

class Export extends ExportMenu
{
    public $exportType = self::FORMAT_CSV;

    /**
     * @return int|void
     */
    public function generateFooter()
    {
        $row = $this->_endRow + $this->_beginRow;
        $footerExists = false;
        $columns = $this->getVisibleColumns();
        if (count($columns) == 0) {
            return;
        }
        $count = 0;
        foreach ($this->getVisibleColumns() as $n => $column) {
            if (is_array($column->footer)) {
                $count = count($column->footer) > $count ? count($column->footer) : $count;
            }
        }
        for ($i = 0 ; $i < $count ; $i++) {

            $this->_endCol = 0;
            foreach ($this->getVisibleColumns() as $n => $column) {
                $this->_endCol = $this->_endCol + 1;
                if ($column->footer) {
                    $footerExists = true;
                    $footer = !is_array($column->footer) ? ($i == 0 ? $column->footer : '') : ($column->footer[$i] ?? '');
                    $footer = trim($footer) !== '' ? $footer : $column->grid->emptyCell;
                    $cell = $this->_objPHPExcel->getActiveSheet()->setCellValue(
                        self::columnName($this->_endCol) . ($row + 1),
                        $footer,
                        true
                    );
                    $this->raiseEvent('onRenderFooterCell', [$cell, $footer, $this]);
                }
            }
            if ($footerExists) $row++;

        }

        return $row;
    }
}