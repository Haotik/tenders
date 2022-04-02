<?php

// Общий класс для создания генераторов MS Office документов
class OfficeDocument extends ZipArchive{

    // Путь к шаблону
    protected $path;

    // Содержимое документа
    protected $content;

    // Множитель для перевода размеров изображений из пикселей в EMU
    protected $px_emu = 8625;

    // Делаем приватно, чтобы не было возможности вшить дрянь в документ
    protected $rels = array();

    public function __construct($filename, $template_path = '/template_xls/' ){

      // Путь к шаблону
      $this->path = dirname(__FILE__) . $template_path;

      // Если не получилось открыть файл, то жизнь бессмысленна.
      if ( $this->open( $filename, ZIPARCHIVE::CREATE) !== TRUE) {
        die("Unable to open <$filename>\n");
      }

      // Описываем связи для документа MS Office
      $this->rels = array_merge( $this->rels, array(
        'rId3' => array(
          'http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties',
          'docProps/app.xml' ),
        'rId2' => array(
          'http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties',
          'docProps/core.xml' ),
      ) );

      // Добавляем типы данных
      $this->addFile($this->path . "[Content_Types].xml" , "[Content_Types].xml" );
    }

    // Генерация зависимостей
    protected function add_rels( $filename, $rels, $path = '' ){

      // Шапка XML
      $xmlstring = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

      // Добавляем документы по описанным связям
      if (!empty($rels)) {
        foreach( $rels as $rId => $params ){

          // Если указан путь к файлу, берем. Если нет, то берем из репозитория
          $pathfile = empty( $params[2] ) ? $this->path . $path . $params[1] : $params[2];

          // Добавляем документ в архив
          if( $this->addFile( $pathfile ,  $path . $params[1] ) === false )
            die('Не удалось добавить в архив ' . $path . $params[1] );

          // Прописываем в связях
          $xmlstring .= '<Relationship Id="' . $rId . '" Type="' . $params[0] . '" Target="' . $params[1] . '"/>';
        }
      }

      $xmlstring .= '</Relationships>';

      // Добавляем в архив
      $this->addFromString( $path . $filename, $xmlstring );
    }

    protected function pparse( $replace, $content ){

      return str_replace( array_keys( $replace ), array_values( $replace ), $content );
    }
}

// Класс для создания документов MS Word
class ExcelDocument extends OfficeDocument{

    public function __construct( $filename, $template_path = '/template_xls/' ){

      parent::__construct( $filename, $template_path );

      // Описываем связи для Word
      $this->xl_rels = array(
        "rId1" => array(
          "http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet",
          "worksheets/sheet1.xml"
        ),
        "rId2" => array(
          "http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme",
          "theme/theme1.xml",
        ),
        "rId3" => array(
          "http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles",
          "styles.xml",
        ),
        "rId4" => array(
          "http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings",
          "sharedStrings.xml",
        ),
      );
    }

    // Упаковываем архив
    public function create($lotes, $orgs, $results_lotes) {

      $this->rels['rId1'] = array(
        'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument', 'xl/workbook.xml' );

      // Добавляем связанные документы MS Office
      $this->add_rels( "_rels/.rels", $this->rels );

      // Добавляем связанные документы MS Office Excel
      $this->add_rels( "_rels/workbook.xml.rels", $this->xl_rels, 'xl/' );
      $this->add_rels( "_rels/sheet1.xml.rels", '', 'xl/worksheets/' );

      $cnt_lotes = (count($lotes) + 3);
      $cnt_cols = ( (count($orgs)*3) + 6);
      $cnt_orgs = count($orgs);
      $group_cols = array(
        array("G", "H", "I"),
        array("J", "K", "L"),
        array("M", "N", "O"),
        array("P", "Q", "R"),
        array("S", "T", "U"),
        array("V", "W", "X"),
        array("Y", "Z", "AA"),
        array("AB", "AC", "AD"),
        array("AE", "AF", "AG"),
        array("AH", "AI", "AJ"),
        array("AK", "AL", "AM"),
        array("AN", "AO", "AP"),
        array("AQ", "AR", "AS"),
        array("AT", "AU", "AV"),
        array("AW", "AX", "AY"),
        array("AZ", "BA", "BB"),
        array("BC", "BD", "BE"),
        array("BF", "BG", "BH"),
        array("BI", "BJ", "BK"),
        array("BL", "BM", "BN"),
        array("BO", "BP", "BQ"),
        array("BR", "BS", "BT"),
        array("BU", "BV", "BW"),
        array("BX", "BY", "BZ"),
        array("CA", "CB", "CC"),
        array("CD", "CE", "CF"),
        array("CG", "CH", "CI"),
        array("CJ", "CK", "CL"),
        array("CM", "CN", "CO"),
        array("CP", "CQ", "CR"),
        array("CS", "CT", "CU"),
        array("CV", "CW", "CX"),
        array("CY", "CZ", "DA"),
        array("DB", "DC", "DD"),
        array("DE", "DF", "DG"),
        array("DH", "DI", "DJ"),
        array("DK", "DL", "DM"),
        array("DN", "DO", "DP"),
        array("DQ", "DR", "DS"),
        array("DT", "DU", "DV"),
        array("DW", "DX", "DY"),
        array("DZ", "EA", "EB"),
        array("EC", "ED", "EE"),
        array("EF", "EG", "EH"),
        array("EI", "EJ", "EK"),
        array("EL", "EM", "EN"),
        array("EO", "EP", "EQ"),
        array("ER", "ES", "ET"),
        array("EU", "EV", "EW"),
        array("EX", "EY", "EZ")
      );

      $str_exit = file_get_contents( $this->path . "xl/worksheets/sheet1.xml" );

      $p = 2;
      $str_repl = "<row r=\"1\" spans=\"1:" . $cnt_cols . "\" x14ac:dyDescent=\"0.25\"><c r=\"A1\" s=\"1\" t=\"s\"><v>0</v></c><c r=\"B1\" s=\"1\" t=\"s\"><v>1</v></c><c r=\"C1\" s=\"1\" t=\"s\"><v>2</v></c><c r=\"D1\" s=\"1\" t=\"s\"><v>3</v></c><c r=\"E1\" s=\"1\" t=\"s\"><v>4</v></c><c r=\"F1\" s=\"1\" t=\"s\"><v>5</v></c>";
      for ($i=0; $i <= ($cnt_orgs-1) ; $i++) {
        $p = $p+4;
        $str_repl .= "<c r=\"" . $group_cols[$i][0] . "1\" s=\"4\" t=\"s\"><v>" . $p . "</v></c><c r=\"" . $group_cols[$i][1] . "1\" s=\"4\"/><c r=\"" . $group_cols[$i][2] . "1\" s=\"4\"/>";
      }
      $str_repl .= "</row><row r=\"2\" spans=\"1:" . $cnt_cols . "\" ht=\"43.5\" customHeight=\"1\" x14ac:dyDescent=\"0.25\"><c r=\"A2\" s=\"1\"/><c r=\"B2\" s=\"1\"/><c r=\"C2\" s=\"1\"/><c r=\"D2\" s=\"1\"/><c r=\"E2\" s=\"1\"/><c r=\"F2\" s=\"1\"/>";
      $o = 6;
      for ($i=0; $i <= ($cnt_orgs-1) ; $i++) {
        $str_repl .= "<c r=\"" . $group_cols[$i][0] . "2\" s=\"5\" t=\"s\"><v>" . ($o+1) . "</v></c><c r=\"" . $group_cols[$i][1] . "2\" s=\"5\" t=\"s\"><v>" . ($o+2) . "</v></c><c r=\"" . $group_cols[$i][2] . "2\" s=\"6\" t=\"s\"><v>" . ($o+3) . "</v></c>";
        $o = $o + 4;
      }
      $str_repl .= "</row>";
      $s = 0;
      $i = 1;
      $clm = 3;
      $total_sum = 0;
      $itogo_orgs = array();
      if (!empty($lotes))
      {
        foreach ($lotes as $key => $value) {
          $total_sum = $total_sum + ($value['need'] * $value['start_sum']);
          $str_repl .= "<row r=\"" . $clm . "\" spans=\"1:" . $cnt_cols . "\" ht=\"18\" customHeight=\"1\" x14ac:dyDescent=\"0.25\"><c r=\"A" . $clm . "\"><v>" . $i . "</v></c><c r=\"B" . $clm . "\" t=\"s\"><v>" . $value['name'] . "</v></c><c r=\"C" . $clm . "\" t=\"s\"><v>" . $value['unit'] . "</v></c><c r=\"D" . $clm . "\"><v>" . $value['need'] . "</v></c><c r=\"E" . $clm . "\"><v>" . $value['start_sum'] . "</v></c><c r=\"F" . $clm . "\"><v>" . ($value['need'] * $value['start_sum']) . "</v></c>";

          foreach ($orgs as $k => $v) {

            // Вычисляем итого для каждого участника
            if (empty($itogo_orgs[$k]))
            {
              $itogo_orgs[$k]['price'] = 0;
              $itogo_orgs[$k]['sum'] = 0;
            }
            $itogo_orgs[$k] = array('price' => ($itogo_orgs[$k]['price'] + $results_lotes[$k][$value['id']]), 'sum' => ($itogo_orgs[$k]['sum'] + ($value['need'] * $results_lotes[$k][$value['id']])) );

            // Рисуем строку для участника (цена, сумма и отклонение)
            $str_repl .= "<c r=\"" . $group_cols[$s][0] . $clm . "\"><v>" . $results_lotes[$k][$value['id']] . "</v></c><c r=\"" . $group_cols[$s][1] . $clm . "\"><v>" . ($value['need'] * $results_lotes[$k][$value['id']]) . "</v></c>" . ($key == 0 ? "<c r=\"" . $group_cols[$s][2] . $clm . "\"><v>{member" . $k . "}</v></c>" : "<c r=\"" . $group_cols[$s][2] . $clm . "\"/>");

            $s++;

          }
          $s = 0;

          $str_repl .= "</row>";
          $clm++;
        }
      }
      // Последняя итоговая строка
      $str_repl .= "<row r=\"" . $clm . "\" spans=\"1:" . $cnt_cols . "\" ht=\"18\" customHeight=\"1\" x14ac:dyDescent=\"0.25\"><c r=\"A" . $clm . "\" t=\"s\"><v>ИТОГО</v></c><c r=\"B" . $clm . "\"/><c r=\"C" . $clm . "\"/><c r=\"D" . $clm . "\"/><c r=\"E" . $clm . "\"/><c r=\"F" . $clm . "\"><v>" . $total_sum . "</v></c>";
      $t = 0;
      foreach ($orgs as $k => $v) {
        $str_repl .= "<c r=\"" . $group_cols[$t][0] . $clm . "\"><v>" . $itogo_orgs[$k]['price'] . "</v></c><c r=\"" . $group_cols[$t][1] . $clm . "\"><v>" . $itogo_orgs[$k]['sum'] . "</v></c><c r=\"" . $group_cols[$t][2] . $clm . "\" t=\"s\"/>";
        $t++;
      }
      $str_repl .= "</row>";

      // Записываем отклонения в строки
      foreach ($orgs as $k => $v) {
        $str_repl = str_replace( '{member' . $k . '}' , ($total_sum - $itogo_orgs[$k]['sum']) , $str_repl );
      }

      $str_exit = str_replace( '{CONTENT}' , $str_repl , $str_exit );

      // Объединение ячеек
      $str_merge = array('<mergeCell ref="A1:A2"/>', '<mergeCell ref="B1:B2"/>', '<mergeCell ref="C1:C2"/>', '<mergeCell ref="D1:D2"/>', '<mergeCell ref="E1:E2"/>', '<mergeCell ref="F1:F2"/>'); // Объединение ячеек в шапке
      $str_merge[] = '<mergeCell ref="A' . $cnt_lotes . ':E' . $cnt_lotes . '"/>'; // Объединение ячеек ИТОГО
      for ($i=0; $i <= ($cnt_orgs-1) ; $i++) {
        $str_merge[] = '<mergeCell ref="' . $group_cols[$i][0] . '1:' . $group_cols[$i][2] . '1"/>';
        $str_merge[] = '<mergeCell ref="' . $group_cols[$i][2] . '3:' . $group_cols[$i][2] . $cnt_lotes . '"/>';
      }

      $str_exit = str_replace( '{MERGECELLS}', "<mergeCells count=\"" . count($str_merge) . "\">" . implode("", $str_merge) . "</mergeCells>", $str_exit );

      // Dimension
      $str_exit = str_replace( '{DIMENSION}', "A1:" . $this->alf($cnt_cols) . $cnt_lotes, $str_exit );

      // Добавляем содержимое
      $this->addFromString("xl/worksheets/sheet1.xml", $str_exit );

      $str_strings_exit = file_get_contents( $this->path . "xl/sharedStrings.xml" );
      $str_columns = "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" count=\"" . ( (count($orgs)*3) + 6) . "\" uniqueCount=\"" . ( (count($orgs)*3) + 6) . "\"><si><t>№ п/п</t></si><si><t>Наименование лота</t></si><si><t>Ед. изм.</t></si><si><t>Потребность</t></si><si><t>Начальная цена</t></si><si><t>Начальная сумма</t></si>";
      foreach ($orgs as $val) {
        $str_columns .= "<si><t>" . $val . "</t></si><si><t>Цена</t></si><si><t>Сумма</t></si><si><t>Отклонение суммы</t></si>";
      }
      $str_columns .= "</sst>";
      $str_strings_exit = str_replace( '{COLUMNS}', $str_columns, $str_strings_exit );

      $this->addFromString("xl/sharedStrings.xml", $str_strings_exit );

      $this->close();
    }

    function alf($col)
    {
      $alf = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

      if ($col > 28)
      {
        $delim = floor($col/28);
        return $alf[($delim-1)] . $alf[( ($col-(28*$delim))-1 )];
      }
      else
        return $alf[($col-1)];
    }
}