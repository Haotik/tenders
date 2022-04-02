<?php

// Общий класс для создания генераторов MS Office документов
class OfficeDocument extends ZipArchive
{

    // Путь к шаблону
    protected $path;

    // Содержимое документа
    protected $content;

    // Множитель для перевода размеров изображений из пикселей в EMU
    protected $px_emu = 8625;

    // Делаем приватно, чтобы не было возможности вшить дрянь в документ
    protected $rels = array();

    public function __construct($filename, $template_path = '/template_doc/')
    {

        // Путь к шаблону
        $this->path = dirname(__FILE__) . $template_path;

        // Если не получилось открыть файл, то жизнь бессмысленна.
        if ($this->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
            die("Unable to open <$filename>\n");
        }

        // Описываем связи для документа MS Office
        $this->rels = array_merge($this->rels, array(
            'rId3' => array(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties',
                'docProps/app.xml'),
            'rId2' => array(
                'http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties',
                'docProps/core.xml'),
        ));

        // Добавляем типы данных
        $this->addFile($this->path . "[Content_Types].xml", "[Content_Types].xml");
    }

    // Генерация зависимостей
    protected function add_rels($filename, $rels, $path = '')
    {

        // Шапка XML
        $xmlstring = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        // Добавляем документы по описанным связям
        foreach ($rels as $rId => $params) {

            // Если указан путь к файлу, берем. Если нет, то берем из репозитория
            $pathfile = empty($params[2]) ? $this->path . $path . $params[1] : $params[2];

            // Добавляем документ в архив
            if ($this->addFile($pathfile, $path . $params[1]) === false)
                die('Не удалось добавить в архив ' . $path . $params[1]);

            // Прописываем в связях
            $xmlstring .= '<Relationship Id="' . $rId . '" Type="' . $params[0] . '" Target="' . $params[1] . '"/>';
        }

        $xmlstring .= '</Relationships>';

        // Добавляем в архив
        $this->addFromString($path . $filename, $xmlstring);
    }

    protected function pparse($replace, $content)
    {

        return str_replace(array_keys($replace), array_values($replace), $content);
    }
}

// Класс для создания документов MS Word
class WordDocument extends OfficeDocument
{

    public function __construct($filename, $template_path = '/template_doc/')
    {

        parent::__construct($filename, $template_path);

        // Описываем связи для Word
        $this->word_rels = array(
            "rId1" => array(
                "http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering",
                "numbering.xml"
            ),
            "rId2" => array(
                "http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles",
                "styles.xml",
            ),
            "rId3" => array(
                "http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings",
                "settings.xml",
            ),
            "rId4" => array(
                "http://schemas.openxmlformats.org/officeDocument/2006/relationships/webSettings",
                "webSettings.xml",
            ),
            "rId6" => array(
                "http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable",
                "fontTable.xml",
            ),
            "rId7" => array(
                "http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme",
                "theme/theme1.xml",
            ),
        );
    }

    // Упаковываем архив
    public function create($arr_content = array(), $array_users = array(), $commission = array())
    {

        $this->rels['rId1'] = array(
            'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument', 'word/document.xml');

        // Добавляем связанные документы MS Office
        $this->add_rels("_rels/.rels", $this->rels);

        // Добавляем связанные документы MS Office Word
        $this->add_rels("_rels/document.xml.rels", $this->word_rels, 'word/');

        // Заменяем параметры тендера
        $str_exit = file_get_contents($this->path . "word/document.xml");
        foreach ($arr_content as $key => $value) {
            $str_exit = str_replace('{' . $key . '}', $value, $str_exit);
        }

        // Рассматривались предложения от следующих  участников
        $str_users = "";
        $i = 1;
        foreach ($array_users as $k => $v) {
            $str_users .= '<w:tr w:rsidR="007A106A"><w:tc><w:tcPr><w:tcW w:w="648" w:type="dxa"/></w:tcPr><w:p w:rsidR="007A106A" w:rsidRDefault="007A106A" w:rsidP="001F6FF8"><w:r><w:t>' . $i . '</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="8923" w:type="dxa"/></w:tcPr><w:p w:rsidR="007A106A" w:rsidRDefault="00B51A25" w:rsidP="004C4C22"><w:r><w:t>' . $k . '</w:t></w:r></w:p></w:tc></w:tr>';
            $i++;
        }
        $str_exit = str_replace('{USERS}', $str_users, $str_exit);

        // Сведения об участниках конкурса
        $str_users = "";
        foreach ($array_users as $k => $v) {
            $str_users .= '<w:tr w:rsidR="00B51A25"><w:trPr><w:trHeight w:val="565"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="3528" w:type="dxa"/></w:tcPr><w:p w:rsidR="00B51A25" w:rsidRDefault="00B51A25" w:rsidP="00BB456E"><w:r><w:t>' . $k . '</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1800" w:type="dxa"/></w:tcPr><w:p w:rsidR="00B51A25" w:rsidRDefault="00B51A25" w:rsidP="00C30BF1"><w:pPr><w:jc w:val="right"/></w:pPr><w:r><w:t>' . $arr_content['TENDER_COST'] . '</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4243" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/></w:tcPr><w:p w:rsidR="00B51A25" w:rsidRDefault="00B51A25" w:rsidP="00934FE2"/></w:tc></w:tr>';
        }
        $str_exit = str_replace('{SVEDUSERS}', $str_users, $str_exit);

        // Предложить участие в открытых торгах с минимальной ценой
        $str_users = "";
        foreach ($array_users as $k => $v) {
            $str_users .= '<w:p w:rsidR="00C15335" w:rsidRDefault="00B51A25" w:rsidP="008C5845"><w:pPr><w:ind w:left="720"/></w:pPr><w:r><w:t>' . $k . '</w:t></w:r></w:p>';
        }
        $str_exit = str_replace('{USERS2}', $str_users, $str_exit);

        // По итогам открытых торгов
        $str_users = "";
        $number = 1;
        foreach ($array_users as $k => $v) {
            $nds = '';
            $str_users .= '<w:tr w:rsidR="00BB456E">
    <w:trPr>
        <w:trHeight w:val="565"/>
    </w:trPr>
    <w:tc>
        <w:tcPr>
            <w:tcW w:w="1000" w:type="dxa"/>
        </w:tcPr>
        <w:p w:rsidR="00BB456E" w:rsidRDefault="00BB456E" w:rsidP="00BB456E">
            <w:r>
                <w:t>' . $number . '</w:t>
            </w:r>
        </w:p>
    </w:tc>
    <w:tc>
        <w:tcPr>
            <w:tcW w:w="3528" w:type="dxa"/>
        </w:tcPr>
        <w:p w:rsidR="00BB456E" w:rsidRDefault="00BB456E" w:rsidP="00BB456E">
            <w:r>
                <w:t>' . $k . '</w:t>
            </w:r>
        </w:p>
    </w:tc>
    <w:tc>
        <w:tcPr>
            <w:tcW w:w="1800" w:type="dxa"/>
        </w:tcPr>
        <w:p w:rsidR="00BB456E" w:rsidRDefault="00BB456E" w:rsidP="00C7247F">
            <w:pPr>
                <w:jc w:val="right"/>
            </w:pPr>
            <w:r>
                <w:t>' . number_format($v, 0, '', ' ') . '</w:t>
            </w:r>
        </w:p>
    </w:tc>
    <w:tc>
        <w:tcPr>
            <w:tcW w:w="4243" w:type="dxa"/>
            <w:shd w:val="clear" w:color="auto" w:fill="auto"/>
        </w:tcPr>
        <w:p w:rsidR="00BB456E" w:rsidRDefault="00BB456E" w:rsidP="00C7247F">
            <w:pPr>
                <w:jc w:val="center"/>
            </w:pPr>
            <w:r>
                <w:t>' . $nds . '</w:t>
            </w:r>
        </w:p>
    </w:tc>
</w:tr>';
            $number++;
        }
        $str_exit = str_replace('{ITOGUSERS}', $str_users, $str_exit);

        // Состав комиссии (в начале)
        $str_users = "";
        if (!empty($commission[2])) {
            foreach ($commission[1] as $key => $value) {
                $str_users .= '<w:tr w:rsidR="00DD14A0" w:rsidTr="00DD14A0"><w:tc><w:tcPr><w:tcW w:w="6941" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:t>' . $value['post'] . '</w:t></w:r></w:p><w:p w:rsidR="00DD14A0" w:rsidRPr="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">' . $value['fio'] . '</w:t></w:r></w:p></w:tc></w:tr>';
            }
        }
        $str_exit = str_replace('{COMMISSION1}', $str_users, $str_exit);

        $str_users = "";
        if (!empty($commission[2])) {
            foreach ($commission[2] as $key => $value) {
                $str_users .= '<w:tr w:rsidR="00DD14A0" w:rsidTr="00DD14A0"><w:tc><w:tcPr><w:tcW w:w="6941" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:t>' . $value['post'] . '</w:t></w:r></w:p><w:p w:rsidR="00DD14A0" w:rsidRPr="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">' . $value['fio'] . '</w:t></w:r></w:p></w:tc></w:tr>';
            }
        }
        $str_exit = str_replace('{COMMISSION2}', $str_users, $str_exit);

        $str_users = "";
        if (!empty($commission[2])) {
            foreach ($commission[3] as $key => $value) {
                $str_users .= '<w:tr w:rsidR="00DD14A0" w:rsidTr="00DD14A0"><w:tc><w:tcPr><w:tcW w:w="6941" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:t>' . $value['post'] . '</w:t></w:r></w:p><w:p w:rsidR="00DD14A0" w:rsidRPr="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">' . $value['fio'] . '</w:t></w:r></w:p></w:tc></w:tr>';
            }
        }
        $str_exit = str_replace('{COMMISSION3}', $str_users, $str_exit);

        // Состав комиссии (в конце)
        $str_users = "";
        if (!empty($commission[2])) {
            foreach ($commission[1] as $key => $value) {
                $str_users .= '<w:tr w:rsidR="00DD14A0" w:rsidTr="00DD14A0"><w:tc><w:tcPr><w:tcW w:w="4537" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:t>' . $value['post'] . '</w:t></w:r></w:p><w:p w:rsidR="00DD14A0" w:rsidRPr="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">____________________</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">' . $value['fio'] . '</w:t></w:r></w:p></w:tc></w:tr>';
            }
        }
        $str_exit = str_replace('{COMMISSION1END}', $str_users, $str_exit);

        $str_users = "";
        if (!empty($commission[2])) {
            foreach ($commission[2] as $key => $value) {
                $str_users .= '<w:tr w:rsidR="00DD14A0" w:rsidTr="00DD14A0"><w:tc><w:tcPr><w:tcW w:w="6941" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:t>' . $value['post'] . '</w:t></w:r></w:p><w:p w:rsidR="00DD14A0" w:rsidRPr="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">____________________</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">' . $value['fio'] . '</w:t></w:r></w:p></w:tc></w:tr>';
            }
        }
        $str_exit = str_replace('{COMMISSION2END}', $str_users, $str_exit);

        $str_users = "";
        if (!empty($commission[2])) {
            foreach ($commission[3] as $key => $value) {
                $str_users .= '<w:tr w:rsidR="00DD14A0" w:rsidTr="00DD14A0"><w:tc><w:tcPr><w:tcW w:w="6941" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r><w:t>' . $value['post'] . '</w:t></w:r></w:p><w:p w:rsidR="00DD14A0" w:rsidRPr="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:pPr><w:rPr><w:lang w:val="en-US"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">____________________</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2404" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DD14A0" w:rsidRDefault="00DD14A0" w:rsidP="001F6FF8"><w:r><w:t xml:space="preserve">' . $value['fio'] . '</w:t></w:r></w:p></w:tc></w:tr>';
            }
        }
        $str_exit = str_replace('{COMMISSION3END}', $str_users, $str_exit);

        // Добавляем содержимое
        $this->addFromString("word/document.xml", $str_exit);

        $this->close();
    }
}