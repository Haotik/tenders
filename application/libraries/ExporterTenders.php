<?php

class ExporterTenders
{
    protected $_file_name;

    public $tenders = array();
    public $classifiers = array();

    public $startIndex = 5;
    protected $currentRow = 0;
    protected $firstTableLastRow = 0;
    protected $secondTableFirstRow = 0;
    /**
     * @var PHPExcel
     */
    private $xl;


    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PHPExcel/PHPExcel.php";


        $this->xl = new PHPExcel();


        foreach (range('A', 'Z') as $colId) {
            $this->getXlSheet()->getColumnDimension($colId)->setWidth(15);
        }

        $this->getXlSheet()->getRowDimension('3')->setRowHeight(30);
        $this->getXlSheet()->getRowDimension('4')->setRowHeight(30);

        $this->setHead();
        $this->currentRow = $this->startIndex;
    }

    private function setHead()
    {
        $this->getXlSheet()->getCell('A1')->setValue("Завершённые аукционы");
        $this->getXlSheet()->mergeCells("A1:C1");
        $this->getXlSheet()->getStyle('A1:C1')
            ->getFont()->setBold(true)->setSize(14);
        $this->getXlSheet()->getStyle('A1:C1')->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
        $this->getXlSheet()->getCell('A3')->setValue("№ п/п");
        $this->getXlSheet()->mergeCells("A3:A4");
        $this->getXlSheet()->getCell('B3')->setValue("Номер на ЭТП");
        $this->getXlSheet()->mergeCells("B3:B4");
        $this->getXlSheet()->getCell('C3')->setValue("Наименование");
        $this->getXlSheet()->mergeCells("C3:C4");
        $this->getXlSheet()->getCell('D3')->setValue("Дата проведения");
        $this->getXlSheet()->mergeCells("D3:D4");
        $this->getXlSheet()->getCell('E3')->setValue("Дата окончания");
        $this->getXlSheet()->mergeCells("E3:E4");
        $this->getXlSheet()->getCell('F3')->setValue("Заказчик");
        $this->getXlSheet()->mergeCells("F3:F4");
        $this->getXlSheet()->getCell('G3')->setValue("Количество лотов");
        $this->getXlSheet()->mergeCells("G3:G4");
        $this->getXlSheet()->getCell('H3')->setValue("Цена");
        $this->getXlSheet()->mergeCells("H3:I3");
        $this->getXlSheet()->getCell('H4')->setValue("Начальная максимальная");
        $this->getXlSheet()->getCell('I4')->setValue("Лучшая");
        $this->getXlSheet()->getCell('J3')->setValue("Кол-во организаций, подавших заявку на участие");
        $this->getXlSheet()->mergeCells("J3:J4");
        $this->getXlSheet()->getCell('K3')->setValue("Количество участников");
        $this->getXlSheet()->mergeCells("K3:K4");
        $this->getXlSheet()->getCell('L3')->setValue("Участники");
        $this->getXlSheet()->mergeCells("L3:L4");
        $this->getXlSheet()->getCell('M3')->setValue("Победитель");
        $this->getXlSheet()->mergeCells("M3:M4");
        $this->getXlSheet()->getCell('N3')->setValue("Отклонения");
        $this->getXlSheet()->mergeCells("N3:O3");
        $this->getXlSheet()->getCell('N4')->setValue("Сумма");
        $this->getXlSheet()->getCell('O4')->setValue("%");
        $this->getXlSheet()->getCell('P3')->setValue("Классификатор");
        $this->getXlSheet()->mergeCells("P3:P4");

        $this->getXlSheet()->getStyle('A3:P4')->getFont()->setBold(true);
        $this->getXlSheet()->getStyle('A3:P4')->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
    }

    private function getXlSheet()
    {
        return $this->xl->getActiveSheet();
    }


    public function generateMainTable()
    {
        $counter = 1;
        foreach ($this->tenders as $k => $model) {
            $this->setXlRow($model, $counter);
            $counter++;
        }
        $this->firstTableLastRow = $this->currentRow;
        $this->currentRow++;

        $this->setCellValue('C', "Итого");
        $this->getXlSheet()->getStyle('C' . $this->currentRow)->getFont()->setBold(true);
        $previous_row = ($this->currentRow - 1);
        $this->setCellValue('G', "=SUM(G5:G{$previous_row})");
        $this->setCellValue('H', "=SUM(H5:H{$previous_row})");
        $this->setCellValue('I', "=SUM(I5:I{$previous_row})");
        $this->setCellValue('K', "=AVERAGE(K5:K{$previous_row})");
        $this->setCellValue('N', "=H{$this->currentRow}-I{$this->currentRow}");
        $this->setCellValue('O', "=N{$this->currentRow}/H{$this->currentRow}");

        $this->getXlSheet()->getStyle("K" . $this->currentRow)
            ->getNumberFormat()->setFormatCode('0');
        $this->getXlSheet()->getStyle("H5:H" . $this->currentRow)
            ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->getXlSheet()->getStyle("I5:I" . $this->currentRow)
            ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->getXlSheet()->getStyle("N5:N" . $this->currentRow)
            ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->getXlSheet()->getStyle("O5:O" . $this->currentRow)
            ->getNumberFormat()->applyFromArray(array(
                "code" => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE
            ));

        $this->currentRow++;
    }

    public function generateBottomTable()
    {
        $this->currentRow += 5;
        $this->getXlSheet()->getCell('F' . $this->currentRow)->setValue("Сводная информация");
        $this->getXlSheet()->getCell('G' . $this->currentRow)->setValue("Количество конкурсов");
        $this->getXlSheet()->getCell('H' . $this->currentRow)->setValue("Начальная цена(максимальная или минимальная)");
        $this->getXlSheet()->getCell('I' . $this->currentRow)->setValue("Итоговая (лучшая) цена");
        $this->getXlSheet()->getCell('J' . $this->currentRow)->setValue("Кол-во организаций, подавших заявку на участие");
        $this->getXlSheet()->getCell('K' . $this->currentRow)->setValue("Количество участников");
        $this->getXlSheet()->getCell('N' . $this->currentRow)->setValue("Экономическая эффективность, руб");
        $this->getXlSheet()->getCell('O' . $this->currentRow)->setValue("Экономическая эффективность, %");

        $this->getXlSheet()->getStyle("F" . $this->currentRow . ":O" . $this->currentRow)->getFont()->setBold(true);
        $this->getXlSheet()->getStyle("F" . $this->currentRow . ":O" . $this->currentRow)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);

        $this->getXlSheet()->getRowDimension($this->currentRow)->setRowHeight(50);

        $this->currentRow++;
        $this->secondTableFirstRow = $this->currentRow;
        foreach ($this->classifiers as $k => $classifier) {
            $this->setCellValue('F', $classifier);
            $this->setCellValue('G',
                '=СУММЕСЛИ($P$5:$P$' . $this->firstTableLastRow . ';$F' . $this->currentRow . ';G$5:G$' . $this->firstTableLastRow . ')',
                's');

            $this->setCellValue('H',
                '=СУММЕСЛИ($P$5:$P$' . $this->firstTableLastRow . ';$F' . $this->currentRow . ';H$5:H$' . $this->firstTableLastRow . ')',
                's');
            $this->setCellValue('I',
                '=СУММЕСЛИ($P$5:$P$' . $this->firstTableLastRow . ';$F' . $this->currentRow . ';I$5:I$' . $this->firstTableLastRow . ')',
                's');
            $this->setCellValue('J',
                '=СУММЕСЛИ($P$5:$P$' . $this->firstTableLastRow . ';$F' . $this->currentRow . ';J$5:J$' . $this->firstTableLastRow . ')',
                's');
            $this->setCellValue('K',
                '=СУММЕСЛИ($P$5:$P$' . $this->firstTableLastRow . ';$F' . $this->currentRow . ';K$5:K$' . $this->firstTableLastRow . ') ',
                's');

            $this->setCellValue('N', "=H{$this->currentRow}-I{$this->currentRow}");

            $this->setCellValue('O', "=N{$this->currentRow}/H{$this->currentRow}");

            $this->currentRow++;
        }

        $this->setCellValue('F', "Итого");
        $this->getXlSheet()->getStyle('F' . $this->currentRow)->getFont()->setBold(true);
        $last_row = $this->currentRow - 1;
        $this->setCellValue('G', "=ИТОГ(9;G{$this->secondTableFirstRow}:G{$last_row})", 's');
        $this->setCellValue('H', "=ИТОГ(9;H{$this->secondTableFirstRow}:H{$last_row})", 's');
        $this->setCellValue('I', "=ИТОГ(9;I{$this->secondTableFirstRow}:I{$last_row})", 's');
        $this->setCellValue('J', "=ИТОГ(9;J{$this->secondTableFirstRow}:J{$last_row})", 's');
        $this->setCellValue('K', "=ИТОГ(9;K{$this->secondTableFirstRow}:K{$last_row})", 's');
        $this->setCellValue('N', "=H{$this->currentRow}-I{$this->currentRow}");
        $this->setCellValue('O', "=N{$this->currentRow}/H{$this->currentRow}");

        $this->currentRow++;
        $this->setCellValue('F', "из них несостоявшиеся");
        $this->getXlSheet()->getStyle('F' . $this->currentRow)->getFont()->setBold(true);
        $last_row = $this->currentRow - 1;
        $this->setCellValue('G', '=СУММЕСЛИ($K$5:$K$' . $this->firstTableLastRow . ';$K' . $this->currentRow . ';G$5:G$' . $this->firstTableLastRow . ')', 's');
        $this->setCellValue('H', '=СУММЕСЛИ($K$5:$K$' . $this->firstTableLastRow . ';$K' . $this->currentRow . ';H$5:H$' . $this->firstTableLastRow . ')', 's');
        $this->setCellValue('I', '=СУММЕСЛИ($K$5:$K$' . $this->firstTableLastRow . ';$K' . $this->currentRow . ';I$5:I$' . $this->firstTableLastRow . ')', 's');
        $this->setCellValue('K', '=СУММЕСЛИ($P$5:$P$' . $this->firstTableLastRow . ';$F' . $this->currentRow . ';K$5:K$' . $this->firstTableLastRow . ')', 's');

        $this->getXlSheet()->getStyle("O{$this->firstTableLastRow}:O" . $this->currentRow)
            ->getNumberFormat()->applyFromArray(array(
                "code" => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE
            ));
    }

    public function generate()
    {
        $this->generateMainTable();
        $this->generateBottomTable();
    }

    public function setXlRow($model, $k, $duplicate = false)
    {
        $this->setCellValue('A', $k);

        $this->setCellValue('B', $model['tender']['id']);
        $this->setCellValue('C', $model['tender']['title']);

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $model['tender']['begin_date']);
        $this->setCellValue('D', PHPExcel_Shared_Date::PHPToExcel($date->format("d.m.Y H:i:s")));
        $this->getXlSheet()->getStyle("D" . $this->currentRow)
            ->getNumberFormat()->setFormatCode("dd/mm/yy hh:mm:ss");

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $model['tender']['end_date']);
        $this->setCellValue('E', PHPExcel_Shared_Date::PHPToExcel($date->format("d.m.Y H:i:s")));
        $this->getXlSheet()->getStyle("E" . $this->currentRow)
            ->getNumberFormat()->setFormatCode("dd/mm/yy hh:mm:ss");

        $this->setCellValue('F', $model['author']['user_name']);
        $this->setCellValue('G', count($model['lotes']));

        $this->setCellValue('H', array_sum(array_map(function ($item) {
            return ($item['start_sum'] * $item['need']);
        }, $model['lotes'])));

        $leader = null;
        foreach ($model['results'] as $result) {
            if ($result['leader'] == 1) {
                $leader = $result;
            }
        }
        if ($leader) {
            $this->setCellValue('I', $leader['total_sum']);
        }
        $this->setCellValue('J', count($model['competitors']));
        $this->setCellValue('K', count($model['results']));
        $this->setCellValue('L', implode(", ", array_map(function ($item) {
            return $item['name'];
        }, $model['competitors'])));
        if ($leader) {
            $this->setCellValue('M', $leader['res_name']);
        }
        $this->setCellValue('N', "=H{$this->currentRow}-I{$this->currentRow}");
        $this->setCellValue('O', "=N{$this->currentRow}/H{$this->currentRow}");

        foreach ($model['tags'] as $tag) {
            $this->classifiers[$tag['caption']] = $tag['caption'];
        }
        $this->setCellValue('P', implode(", ", array_map(function ($item) {
            return $item['caption'];
        }, $model['tags'])));

        $this->currentRow++;
    }

    private function setCellValue($cellLetter, $value, $type = false)
    {
        if ($type !== false) {
            $this->getXlSheet()->setCellValueExplicit($cellLetter . $this->currentRow, $value, $type);

            return;
        }
        $this->getXlSheet()->getCell($cellLetter . $this->currentRow)->setValue($value);

        return;
    }

    public function export()
    {
        $this->generate();
        @mkdir($this->getContentDirPath(), 0777, true);
//        $writer = new PHPExcel_Writer_Excel5($this->xl);
        $writer = PHPExcel_IOFactory::createWriter($this->xl, 'Excel2007');
        ob_end_clean();
        $writer->save($this->getContentDirPath() . $this->getFileName());

        return $this->getContentDirUrl() . $this->getFileName();
    }


    public function getContentDirUrl()
    {
        return '/data/export_tenders/' . date("Y") . '/' . date("m") . '/' . date("d") . '/';
    }

    public function getContentDirPath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->getContentDirUrl();
    }

    public function getFileName()
    {
        if (!$this->_file_name) {
            $this->_file_name = "export-tenders-" . date('Y-m-d_H_i_s') . ".xlsx";
        }

        return $this->_file_name;
    }

    protected function runDownloadHeaders()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($this->getContentDirPath() . $this->getFileName()));
        header('Content-Disposition: attachment; filename=' . basename($this->getFileName()));
        echo file_get_contents($this->getContentDirPath() . $this->getFileName());
    }

    private function changeCellColor($cell, $color)
    {
        $this->getXlSheet()->getStyle($cell)->getFill()->applyFromArray(array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => $color
            )
        ));
    }

}