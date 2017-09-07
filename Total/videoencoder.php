<?php

class videoEncoder
{
	private $src;
	private $dst;
	private $vb;
	private $ab;
	private $format;
	private $size;
	private $width;
	private $height;
	private $ip 			= __MALAKOFF_SERVER_IP_INT;
	private  $ffmpeg 		= '';
	private  $ffmpeg2theora = 'ffmpeg2theora';
	private  $qtfast 		= 'qt-faststart';
	private $tracelog			= 'media/tmp/encoding.log';
	function __construct()
	{
		if($_SERVER['SERVER_ADDR']==$this->ip)	
			$this->ffmpeg 	= '/usr/local/bin/ffmpeg ';
		else
			$this->ffmpeg 	= '/usr/local/bin/ffmpeg ';
	}
	function encode($src, $dst, $param)
	{
		$this->format 	= $param['s_format'];
		$this->src		= $param['s_file'];
		$this->dst 		= $param['s_output'];
		$this->vb 		= $param['s_vbrate'];
		$this->ab 		= $param['s_abrate'];
		$this->size		= $param['s_size'];
		list($this->width,$this->height)	= explode("x",$this->size);
		switch($this->format)
		{
			case 'mov':
				$this->movEncode();
				break;
			case 'avi':
				$this->aviEncode();
				break;
			case 'mp4':
				$this->mp4Encode();
				break;
			case 'swf':
				$this->swfEncode();
				break;
			case 'ogg':
			case 'ogv':
				$this->oggEncode();
				break;
			case 'webm':
				$this->webmEncode();
				break;
			case 'flv':
				$this->flvEncode();
				break;
		}
	}
	
	
	// transcoder de l'apple pro res avec l'option -pix_fmt yuv420p	
	function movEncode()
	{
		$cmd = $this->ffmpeg." -i '".$this->src."' -y -pix_fmt yuv420p -b:v ".$this->vb." -ar 44100 -b:a ".$this->ab." -s ".$this->size." '".$this->dst."'";
		//system($cmd." 2>".$this->tracelog);
		system($cmd );
	}
	function aviEncode()
	{
		$cmd = $this->ffmpeg." -i '".$this->src."' -y -pix_fmt yuv420p -b:v ".$this->vb." -ar 44100 -b:a ".$this->ab." -s ".$this->size." '".$this->dst."'";
		//system($cmd." 2>".$this->tracelog);
		system($cmd );
	}
	function mp4Encode()
	{
		if($_SERVER['SERVER_ADDR']==$this->ip)	
			$cmd = $this->ffmpeg." -i '".$this->src."' -movflags faststart -y -strict experimental -f mp4  -pix_fmt yuv420p -vcodec libx264 -b:v ".$this->vb." -acodec aac -ar 44100 -b:a ".$this->ab." -ac 2 -crf 22 -s ".$this->size." '".$this->dst."'";
		else
			$cmd = $this->ffmpeg." -i '".$this->src."' -movflags faststart -y -strict experimental -f mp4  -pix_fmt yuv420p -vcodec libx264 -b ".$this->vb." -acodec aac -ar 44100 -ab ".$this->ab." -ac 2 -crf 22 -s ".$this->size." '".$this->dst."'";
		echo $cmd."<br/>";
		//system($cmd ." 2>".$this->tracelog);
		system($cmd );
	//	$this->swapHeader($this->dst);
	}
	function swfEncode()
	{
		$cmd = $this->ffmpeg." -i '".$this->src."' -y -r 25 -b:v ".$this->vb." -ar 44100 -b:a ".$this->ab." -s ".$this->size." '".$this->dst."'";
		//system($cmd." 2>".$this->tracelog);
		system($cmd );
	}
	function oggEncode()
	{
		list($w,$h) = explode("x",$this->size);
		
		if($_SERVER['SERVER_ADDR']==$this->ip)	
			$cmd = "ffmpeg -i '".$this->src."' -y -strict experimental -f mp4  -pix_fmt yuv420p -vcodec libx264 -b:v 10000k -acodec aac -ar 44100 -b:a 256k -ac 2 -crf 22  '".$this->dst.".mp4'";
		else
			$cmd = "ffmpeg -i '".$this->src."' -y -strict experimental -f mp4  -pix_fmt yuv420p -vcodec libx264 -b 10000k -acodec aac -ar 44100 -ab 256k -ac 2 -crf 22  '".$this->dst.".mp4'";

		system($cmd);
		$cmd = $this->ffmpeg2theora." '".$this->dst.".mp4' -V ".$this->vb." -A ".$this->ab." --width $w --height $h  -o '".$this->dst."'";	

		system($cmd);
		unlink($this->dst.".mp4");
	}
	function webmEncode()
	{
		
		$cmd = $this->ffmpeg." -i '".$this->src."' -y  -f webm  -vcodec libvpx -b:v ".$this->vb." -acodec libvorbis -ar 44100 -b:a ".$this->ab." -crf 22 -s ".$this->size." '".$this->dst."'";
		//system($cmd." 2>".$this->tracelog);
		system($cmd );
	}
	function flvEncode()
	{
		if($_SERVER['SERVER_ADDR']==$this->ip)	
			$cmd = $this->ffmpeg." -i '".$this->src."' -y  -f flv   -b:v ".$this->vb."  -ar 44100 -b:a ".$this->ab."  -s ".$this->size." '".$this->dst."'";
		else
			$cmd = $this->ffmpeg." -i '".$this->src."' -y  -f flv   -b ".$this->vb."  -ar 44100 -ab ".$this->ab."  -s ".$this->size." '".$this->dst."'";
		//system($cmd." 2>".$this->tracelog);
		system($cmd );
	}
	
	
	
	
	
	static function makeThumbnails($video, $output)
	{
		$name = "media/tmp/".md5($output).".jpg";
		$cmd = "ffmpeg -itsoffset -4 -i '".$video."' -vframes 1 -s 133x75 ".$name;

		system($cmd);
		return $name;
	}
	
	private function swapHeader($f)
	{
		$tmp 	= "media/tmp/".rand(1000,9999).".mp4";
		rename($f, $tmp);
		$cmd	= $this->qtfast." ".$tmp." '".$f."'";
		system($cmd);		
	}
	
	static function getProgress()
	{
		$content = file_get_contents("media/tmp/encoding.log");
		// # get duration of source
		preg_match("/Duration: (.*?), start:/", $content, $matches);
		
		$rawDuration = @$matches[1];
		
		// # rawDuration is in 00:00:00.00 format. This converts it to seconds.
		$ar = array_reverse(explode(":", $rawDuration));
		$duration = floatval($ar[0]);
		if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
		if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;
		echo $duration;
		
		// # get the current time
		preg_match_all("/time=(.*?) bitrate/", $content, $matches); 
		
		$last = array_pop($matches);
		// # this is needed if there is more than one match
		if (is_array($last)) {
		    $last = array_pop($last);
		}
		$ar = array_reverse(explode(":", $last));
		$curTime = floatval($ar[0]);
		if (!empty($ar[1])) $curTime += intval($ar[1]) * 60;
		if (!empty($ar[2])) $curTime += intval($ar[2]) * 60 * 60;

		// # finally, progress is easy
		if($duration)
			$progress = 100 * $curTime/$duration;
		else {
			$progress = 0;
		}
		return round($progress).' %';
	}
	
}



?>