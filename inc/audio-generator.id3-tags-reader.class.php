<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

class ID3TagsReader {

    // variables
    var $aTV23 = array( // array of possible sys tags (for last version of ID3)
        'TIT2' => 'Title',
        'TALB' => 'Album',
        'TPE1' => 'Author',
        'TPE2' => 'AlbumAuthor',
        'TRCK' => 'Track',
        'TYER' => 'Year',
        'TLEN' => 'Length',
        'USLT' => 'Lyric',
        'TPOS' => 'Desc',
        'TCON' => 'Genre',
        'TENC' => 'Encoded',
        'TCOP' => 'Copyright',
        'TPUB' => 'Publisher',
        'TOPE' => 'OriginalArtist',
        'WXXX' => 'URL',
        'COMM' => 'Comments',
        'TCOM' => 'Composer'
    );
    var $aTV22 = array( // array of possible sys tags (for old version of ID3)
        'TT2' => 'Title',
        'TAL' => 'Album',
        'TP1' => 'Author',
        'TRK' => 'Track',
        'TYE' => 'Year',
        'TLE' => 'Length',
        'ULT' => 'Lyric',
    );

    // constructor
    function ID3TagsReader() {}

    // functions

	function getAvailabel23Tags(){
		return $this->aTV23;
	}

    function getTagsInfo($sFilepath) {
        // read source file
        $iFSize = filesize($sFilepath);
        $vFD = fopen($sFilepath,'r');
        $sSrc = fread($vFD,$iFSize);
        fclose($vFD);

        // obtain base info
        if (substr($sSrc,0,3) == 'ID3') {
            $aInfo['FileName'] = $sFilepath;
			$aInfo['Version'] = hexdec(bin2hex(substr($sSrc,3,1))).'.'
								.hexdec(bin2hex(substr($sSrc,4,1)));
        }

        // passing through possible tags of idv2 (v3 and v4)
        if ($aInfo['Version'] == '4.0' || $aInfo['Version'] == '3.0') {
			foreach ($this->aTV23 as $tag => $name){
                if (strpos($sSrc, $tag . chr(0)) != FALSE) {

                    $s = '';
                    $iPos = strpos($sSrc, $tag.chr(0));
                    $iLen = hexdec(bin2hex(substr($sSrc,($iPos + 5),3)));
                    $iEnc = hexdec(bin2hex(substr($sSrc,($iPos + 9),2)));

                    $data = substr($sSrc, $iPos, 9 + $iLen);
					if($iEnc) { //is Unicode
						if(strpos(bin2hex($data),"fffe") !== false){
							//LittleEndian
							$aData = explode("fffe", bin2hex($data));
							$contend = array_pop($aData);
							$s .= mb_convert_encoding(pack("H*", $contend),
														"UTF-8", "UTF-16LE");
						}else{
							//BigEndian
							$aData = explode("feff", bin2hex($data));
							$contend = array_pop($aData);
							$s .= mb_convert_encoding(pack("H*", $contend),
														"UTF-8", "UTF-16BE");
						}
					}else{
						$contend = array_pop(explode("00", bin2hex($data)));
						$s .= mb_convert_encoding(pack("H*", $contend),
														"UTF-8", "ISO-8859-1");
					}
                    $aInfo[$tag] = $s;
                }
            }
        }

        // passing through possible tags of idv2 (v2)
        if($aInfo['Version'] == '2.0') {
            foreach ($this->aTV22 as $tag => $name) {
                if (strpos($sSrc, $tag . chr(0)) != FALSE) {

                    $s = '';
                    $iPos = strpos($sSrc, $tag.chr(0));
                    $iLen = hexdec(bin2hex(substr($sSrc,($iPos + 3),3)));

                    $data = substr($sSrc, $iPos, 6 + $iLen);
                    for ($a = 0; $a < strlen($data); $a++) {
                        $char = substr($data, $a, 1);
                        if ($char >= ' ' && $char <= '~')
                            $s .= $char;
                    }

                    if (substr($s, 0, 3) == $tag) {
                        $iSL = 3;
                        if ($tag == 'ULT') {
                            $iSL = 6;
                        }
                        $aInfo[$tag] = substr($s, $iSL);
                    }
                }
            }
        }
        return $aInfo;
    }
}
