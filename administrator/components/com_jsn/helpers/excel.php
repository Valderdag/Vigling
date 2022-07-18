<?php defined('_JEXEC') or die; // No direct access

/********************************
 * WB Tube project (v.0.6 May 2019)
 * Excel file support
 ********************************/

jimport('joomla.filesystem.file');

class WTExcelFile extends JFile
{
	public		$filename; 
	protected	$strings;
	protected	$zip;
	protected	$needDel;
	public		$sheets;
	public		$sheet_names;
	public		$errors;

	public function __construct($fname, $needUpload=TRUE)
	{
		$this->needDel = $needUpload;
		if($needUpload)
			$this->tmp_upload($fname);
		else $this->filename = $fname;
		$this->zip = new ZipArchive;
		$res = $this->zip->open($this->filename, ZipArchive::CHECKCONS);
		if($res!==TRUE)
			$this->errors = $res;
		else $this->xlsx_loadStrings();
	}

	public function __destruct()
	{
		if($this->errors)
			return;
		$this->zip->close();
		if($this->needDel &&  file_exists($this->filename))
		   JFile::delete($this->filename);
	}

	protected function xlsx_loadStrings()
	{
		$dom = new DOMDocument;
		$file1 = $this->zip->locateName("xl/sharedStrings.xml", ZIPARCHIVE::FL_NOCASE);
		$dom->loadXML($this->zip->getFromIndex($file1), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
		$tags = $dom->documentElement->getElementsByTagName('si');
		foreach($tags as $tag)
		   $this->strings[] = $tag->nodeValue;
		file_put_contents(JPATH_SITE.'/tmp/str.txt', print_r($dom, 1));
	}
	
	public function xlsx_getWorkbook()
	{
		$dom = new DOMDocument;
		$wb = $this->zip->locateName("xl/workbook.xml", ZIPARCHIVE::FL_NOCASE);
		$dom->loadXML($this->zip->getFromIndex($wb), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

		$sheets = $dom->documentElement->getElementsByTagName('sheet');
		$results = array();
		foreach($sheets as $sheet){
		   $sheet_name = $sheet->getAttribute('name');
		   $sheet_id = $sheet->getAttribute('sheetId');
		   $results[$sheet_id] = $sheet_name;
		}
		return $results;
	}

	public function tmp_upload($tmp_name)
	{
		$config	=& JFactory::getConfig();
		$this->filename = $config->get('tmp_path').'/'.basename($tmp_name);
		JFile::upload($tmp_name, $this->filename);
	}

	public function xlsx_getSheetData($sheet_id)
	{		
		$dom = new DOMDocument;
		$sheet = $this->zip->locateName("xl/worksheets/sheet".$sheet_id.".xml", ZIPARCHIVE::FL_NOCASE);
		$dom->loadXML($this->zip->getFromIndex($sheet), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
		$rows = $dom->documentElement->getElementsByTagName('row');

		$table = array();		
		foreach($rows as $row){
			$row_num = $row->getAttribute('r');
			$cells = $row->getElementsByTagName('c');
			$row_arr = $this->xlsx_Row2Array($cells, $row_num);
			$table[$row_num]=$row_arr;
		}
		return $table;
	}

	public function xlsx_getColumnNum($idx)
	{
		if (strlen($idx) == 1)
		  return (ord($idx{0}) - 64);
		else return $result = ((1 + (ord($idx{0}) - 65)) * 26) + (ord($idx{1}) - 64);
	}

	public function xlsx_getColumnLetter($num = 0)
	{
		if($num < 26)
            return chr($num + 64);
		return $this->xlsx_getColumnLetter((int)($num / 26) - 1).chr(65 + $num % 26) ;
	}

	public function xlsx_setSheetData($sheet_name, $table, $fmts)
	{
		$sheet = new DOMDocument('1.0', "UTF-8");
		$sheet->formatOutput = true;
		$sheet->xmlStandalone = true;
		$sheetId = count($this->sheets)+1;
		$sheet->xmlname='sheet'.$sheetId.'.xml';
		$root = $sheet->createElementNS("http://schemas.openxmlformats.org/spreadsheetml/2006/main", 'worksheet');
		$root->setAttributeNode(new DOMAttr('xmlns:mx', "http://schemas.microsoft.com/office/mac/excel/2008/main"));
		$root->setAttributeNode(new DOMAttr('xmlns:mc', "http://schemas.openxmlformats.org/markup-compatibility/2006"));
		$root->setAttributeNode(new DOMAttr('xmlns:r', "http://schemas.openxmlformats.org/officeDocument/2006/relationships"));
		$root->setAttributeNode(new DOMAttr('xmlns:x14', "http://schemas.microsoft.com/office/spreadsheetml/2009/9/main"));
		$root->setAttributeNode(new DOMAttr('xmlns:x14ac', "http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac"));
		$root->setAttributeNode(new DOMAttr('xmlns:xm', "http://schemas.microsoft.com/office/excel/2006/main"));
		$sh_views = $sheet->createElement('sheetViews');
		$sh_views->appendChild($this->createWithAttributes($sheet, 'sheetView', array('workbookViewId'=>"0")));
		$root->appendChild($sh_views);
		$root->appendChild($this->createWithAttributes($sheet, 'sheetFormatPr', array('customHeight'=>"1", 'defaultColWidth'=>"17.29", 'defaultRowHeight'=>"15.75")));
		$sheet_data = $sheet->createElement('sheetData');
		foreach($table as $line=>$row)
			$sheet_data->appendChild($this->xlsx_Array2Row($sheet, $line, $row, $fmts[$line]));
		$root->appendChild($sheet_data);
		$sheet->appendChild($root);
		$this->sheets[$sheetId]= $sheet;
		$this->sheet_names[$sheetId] = $sheet_name;
	}
	
	protected function xlsx_Array2Row($sheet, $line, $row, $fmt)
	{
		$row_el = $this->createWithAttributes($sheet, 'row', array('customHeight'=>1, 'r'=>$line, 'ht'=>"11.25"));
		foreach($row as $col=>$data){
			$cell = $sheet->createElement('c');
			$cell->setAttribute('r', $col);
			if($fmt[$col]=='s')
			 if(!empty($data)){
			   $cell->setAttribute('t', "s");
			   $sid = array_search($data, $this->strings);
			   if($sid)
				$data = $sid;
			   else {
				$this->strings[] = $data;
				$data = count($this->strings)-1;
			   }
			  
				
			}
			$cell->appendChild($sheet->createElement('v', $data));
			$row_el->appendChild($cell);
			}
		return $row_el;
	}

	protected function xlsx_Row2Array($cells, $row_num)
	{
		$row = array();	
		foreach($cells as $cell) {
			$addr = $cell->getAttribute('r');
			$addr = str_replace($row_num, '', $addr);
			$type = $cell->getAttribute('t');
			$val = $cell->nodeValue;
			if(!$val && ($type!='n'))
				$row[$addr]='';
			elseif($type=='s')
			   $row[$addr]=$this->strings[(int)$val];
			elseif($type=='str')
				$row[$addr]=$val;  
			else $row[$addr]=(float)$val;	
		}	
		return $row;
	}

	public function xlsx_Save($path)
	{
		if(empty($this->zip))
		   $this->zip = new ZipArchive;
		if (!$this->zip->open($this->filename, ZipArchive::CREATE)) {
		   JError::raiseWarning('', print_r("unable to create zip", true));
		   return; 
		}
		$this->zip->addFromString("[Content_Types].xml", $this->xlsx_buildContentTypes());
		$this->zip->addEmptyDir("_rels/");
		$this->zip->addFromString("_rels/.rels", $this->xlsx_buildRels());
		$this->zip->addEmptyDir("xl/_rels/");
		$this->zip->addFromString("xl/_rels/workbook.xml.rels", $this->xlsx_buildWbRels());		
		
		$wb = new DOMDocument('1.0', "UTF-8");
		$wb->formatOutput = true;
		$wb->xmlStandalone = true;
		$root = $wb->createElementNS("http://schemas.openxmlformats.org/spreadsheetml/2006/main", 'workbook');
		$root->setAttributeNode(new DOMAttr('xmlns:mx', "http://schemas.microsoft.com/office/mac/excel/2008/main"));
		$root->setAttributeNode(new DOMAttr('xmlns:mc', "http://schemas.openxmlformats.org/markup-compatibility/2006"));
		$root->setAttributeNode(new DOMAttr('xmlns:mv', "urn:schemas-microsoft-com:mac:vml"));
		$root->setAttributeNode(new DOMAttr('xmlns:r', "http://schemas.openxmlformats.org/officeDocument/2006/relationships"));
		$root->setAttributeNode(new DOMAttr('xmlns:x14', "http://schemas.microsoft.com/office/spreadsheetml/2009/9/main"));
		$root->setAttributeNode(new DOMAttr('xmlns:x14ac', "http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac"));
		$sheets_el = $wb->createElement('sheets');
		$root->appendChild($wb->createElement('definedNames'));
		$root->appendChild($wb->createElement('calcPr'));		
		foreach($this->sheets as $shId=>$sheet){
			$sheet_el = $this->createWithAttributes($wb, 'sheet', array('sheetId'=>$shId, 
				'name'=>$this->sheet_names[$shId], 'state'=>'visible', 'r:id'=>"rId3"));
			$sheets_el->appendChild($sheet_el);
		}		
		$root->appendChild($sheets_el);
		$wb->appendChild($root);
		$this->zip->addFromString("xl/workbook.xml", $wb->saveXML());
		$this->zip->addEmptyDir("xl/worksheets/");
		JError::raiseWarning('', print_r($sheets_el, true));
		foreach($this->sheets as $sheet){			
			$this->zip->addFromString("xl/worksheets/".$sheet->xmlname, $sheet->saveXML());
		}

		file_put_contents($path."/sharedStrings.xml", $this->xlsx_writeSharedString());		
		file_put_contents($path."/styles.xml", $this->xlsx_writeStyles());

		$this->zip->addFromString("xl/sharedStrings.xml", $this->xlsx_writeSharedString());
		$this->zip->addFromString("xl/styles.xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		  .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"><numFmts count="0"></styleSheet>');
		$this->zip->addEmptyDir("xl/drawings/");
		$this->zip->addFromString("xl/drawings/drawing1.xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		  .'<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:dgm="http://schemas.openxmlformats.org/drawingml/2006/diagram"/>');
		$this->zip->addEmptyDir("xl/worksheets/_rels");
		$this->zip->addFromString("xl/worksheets/_rels/sheet1.xml.rels", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		  .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Target="../drawings/drawing1.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Id="rId1"/></Relationships>');
	}

	public function createWithAttributes($root, $elem_name, $attibutes)
	{
		$elem = $root->createElement($elem_name);
		foreach($attibutes as $key=>$value)
			$elem->setAttributeNode(new DOMAttr($key, $value));
		return $elem;
	}

	public function xlsx_buildRels()
	{
		$rels_xml='<?xml version="1.0" encoding="UTF-8"?>'."\n".'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
		$rels_xml.='<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
		$rels_xml.="\n".'</Relationships>';
		return $rels_xml;
	}
	
	protected function xlsx_buildWbRels()
	{
		$i=0;
		$wkbkrels_xml="";
		$wkbkrels_xml.='<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$wkbkrels_xml.='<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
		$wkbkrels_xml.='<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
		foreach($this->sheets as $sheet_name=>$sheet) {
			$wkbkrels_xml.='<Relationship Id="rId'.($i+2).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/'.($sheet->xmlname).'"/>';
			$i++;
		}
		if (!empty($this->strings)) {
			$wkbkrels_xml.='<Relationship Id="rId'.(count($this->sheets)+2).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
		}
		$wkbkrels_xml.="\n".'</Relationships>';
		return $wkbkrels_xml;
	}

	protected function xlsx_writeSharedString()
	{
		$sstr = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$sstr .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n";
		foreach($this->strings as $str)
		   $sstr .= '<si><t>'.$str.'</t></si>';		
		return $sstr.'</sst>';
	}
	
	protected function xlsx_buildContentTypes()
	{
		$ctypes = new DOMDocument('1.0', "UTF-8");
		$ctypes->formatOutput = true;
		$ctypes->xmlStandalone = true;
		$root = $ctypes->createElementNS("http://schemas.openxmlformats.org/package/2006/content-types", 'Types');
		$root->appendChild($this->createWithAttributes($ctypes, 'Default', array('Extension'=>"rels", 'ContentType'=>"application/vnd.openxmlformats-package.relationships+xml")));
		$root->appendChild($this->createWithAttributes($ctypes, 'Default', array('Extension'=>"xml", 'ContentType'=>"application/xml")));
		$root->appendChild($this->createWithAttributes($ctypes, 'Override', 
			array('PartName'=>"/xl/drawings/drawing1.xml", 'ContentType'=>"application/vnd.openxmlformats-officedocument.drawing+xml")));
		$root->appendChild($this->createWithAttributes($ctypes, 'Override', 
			array('PartName'=>"/xl/styles.xml", 'ContentType'=>"application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml")));
	 	$root->appendChild($this->createWithAttributes($ctypes, 'Override', 
			array('PartName'=>"/xl/sharedStrings.xml", 'ContentType'=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml")));
		$root->appendChild($this->createWithAttributes($ctypes, 'Override', 
			array('PartName'=>"/xl/workbook.xml", 'ContentType'=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml")));
		foreach($this->sheets as $sheet_name=>$sheet)
		   $root->appendChild($this->createWithAttributes($ctypes, 'Override', 
			array('PartName'=>"/xl/worksheets/".$sheet->xmlname, 'ContentType'=>"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml")));
		$ctypes->appendChild($root);		
		return (string)$ctypes->saveXML();		
	}
	
}
