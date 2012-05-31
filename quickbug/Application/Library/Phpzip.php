<?php
/**
 * PHP ZIP 压缩工具
 *
 * [使用方法]
 *
 * if(PHPZip::check()){
 *	PHPZip::create('/data/t.txt','/data/a.zip');
 * }
 *
 */
class PHPZip
{
	// 构造函数
	public function __construct(){
		if(!self::check()){
			die('zlib library not install');
		}
	}

	// 判断系统是否安装的ZIP的环境
	public static function check(){
		return function_exists('gzcompress');
	}

	/**
	 * 将文件压缩成对应ZIP
	 *
	 * @param string $datafilename 文件名或内容
	 * @param string $zipfilename ZIP文件名
	 * @param int $type 类型 0:$datafilename是文件名 1:$datafilename是内容
	 * @param string $showname 当 $type=1 时显示的文件名
	 */
	public static function create($datafilename, $zipfilename,$type=0,$showname='')
	{
		$zip = new self();
		if($type == 0){
			$filename = basename($datafilename);
			$data = file_get_contents($datafilename);
		}else{
			$filename = basename($showname);
			$data = $datafilename;
		}
		$zip->addFile($data, $filename);
		file_put_contents($zipfilename,$zip->filezip());
		return true;
	}

	/* ====================== 以下压缩算法来自网络 ========================= */
	private $datasec      = array();
	private $ctrl_dir     = array();
	private $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	private $old_offset   = 0;

	/**
	 * Converts an Unix timestamp to a four byte DOS date and time format (date
	 * in high two bytes, time in low two bytes allowing magnitude comparison).
	 *
	 * @param  integer  the current Unix timestamp
	 *
	 * @return integer  the current date in a four byte DOS format
	 *
	 * @access private
	 */
	function unix2DosTime($unixtime = 0) {
		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
		if ($timearray['year'] < 1980) {
			$timearray['year']    = 1980;
			$timearray['mon']     = 1;
			$timearray['mday']    = 1;
			$timearray['hours']   = 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		} // end if
		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
		($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	} // end of the 'unix2DosTime()' method


	/**
	 * Adds "file" to archive
	 *
	 * @param  string   file contents
	 * @param  string   name of the file in the archive (may contains the path)
	 * @param  integer  the current timestamp
	 *
	 * @access public
	 */
	public function addFile($data, $name, $time = 0)
	{
		$name     = str_replace('\\', '/', $name);
		$dtime    = dechex($this->unix2DosTime($time));
		$hexdtime = '\x' . $dtime[6] . $dtime[7]
		. '\x' . $dtime[4] . $dtime[5]
		. '\x' . $dtime[2] . $dtime[3]
		. '\x' . $dtime[0] . $dtime[1];
		eval('$hexdtime = "' . $hexdtime . '";');

		$fr   = "\x50\x4b\x03\x04";
		$fr   .= "\x14\x00";            // ver needed to extract
		$fr   .= "\x00\x00";            // gen purpose bit flag
		$fr   .= "\x08\x00";            // compression method
		$fr   .= $hexdtime;             // last mod time and date

		// "local file header" segment
		$unc_len = strlen($data);
		$crc     = crc32($data);
		$zdata   = gzcompress($data);
		$c_len   = strlen($zdata);
		$zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
		$fr      .= pack('V', $crc);             // crc32
		$fr      .= pack('V', $c_len);           // compressed filesize
		$fr      .= pack('V', $unc_len);         // uncompressed filesize
		$fr      .= pack('v', strlen($name));    // length of filename
		$fr      .= pack('v', 0);                // extra field length
		$fr      .= $name;

		// "file data" segment
		$fr .= $zdata;

		// "data descriptor" segment (optional but necessary if archive is not
		// served as file)
		$fr .= pack('V', $crc);                 // crc32
		$fr .= pack('V', $c_len);               // compressed filesize
		$fr .= pack('V', $unc_len);             // uncompressed filesize

		// add this entry to array
		$this -> datasec[] = $fr;
		$new_offset = strlen(implode('', $this->datasec));

		// now add to central directory record
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .= "\x00\x00";                // version made by
		$cdrec .= "\x14\x00";                // version needed to extract
		$cdrec .= "\x00\x00";                // gen purpose bit flag
		$cdrec .= "\x08\x00";                // compression method
		$cdrec .= $hexdtime;                 // last mod time & date
		$cdrec .= pack('V', $crc);           // crc32
		$cdrec .= pack('V', $c_len);         // compressed filesize
		$cdrec .= pack('V', $unc_len);       // uncompressed filesize
		$cdrec .= pack('v', strlen($name) ); // length of filename
		$cdrec .= pack('v', 0 );             // extra field length
		$cdrec .= pack('v', 0 );             // file comment length
		$cdrec .= pack('v', 0 );             // disk number start
		$cdrec .= pack('v', 0 );             // internal file attributes
		$cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set

		$cdrec .= pack('V', $this -> old_offset ); // relative offset of local header
		$this -> old_offset = $new_offset;

		$cdrec .= $name;

		// optional extra field, file comment goes here
		// save to central directory
		$this -> ctrl_dir[] = $cdrec;
	} // end of the 'addFile()' method


	/**
	 * Dumps out file
	 *
	 * @return  string  the zipped file
	 *
	 * @access public
	 */
	public function filezip()
	{
		$data    = implode('', $this -> datasec);
		$ctrldir = implode('', $this -> ctrl_dir);
		return
		$data .
		$ctrldir .
		$this -> eof_ctrl_dir .
		pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
		pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
		pack('V', strlen($ctrldir)) .           // size of central dir
		pack('V', strlen($data)) .              // offset to start of central dir
		"\x00\x00";                             // .zip file comment length
	} // end of the 'filezip()' method

} // end of the 'PHPZip' class
