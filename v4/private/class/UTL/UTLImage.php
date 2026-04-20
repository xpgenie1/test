<?php
require_once 'v4/private/class/UTL/UTLUtilidades.php';

class UTLImage{
	
	/**
	 * Recorte de imágen
	 * @param int $crop_x
	 * @param int $crop_y
	 * @param int $crop_width
	 * @param int $crop_height
	 * @param int $image_width
	 * @param int $image_height
	 * @param string $background Color in HEX format (#ffffff) or 'transparent' string
	 * @return GDImageResource
	 */
	public static function cut($img_path,$crop_x,$crop_y,$crop_width,$crop_height,$image_width,$image_height,$background){
		
		$format='jpg';

		if ($background=='transparent'){
			$format='png';
		}

		$extension=strtolower(strrchr($img_path,'.'));
		
		$img_origen=null;
		
		//imagen original
		switch($extension){
			case '.jpg':
			case '.jpeg':
				$img_origen=imagecreatefromjpeg($img_path);
				break;
			case '.png':
				$img_origen=imagecreatefrompng($img_path);
				break;
			case '.gif':
				$img_origen=imagecreatefromgif($img_path);
				break;
		}
		

		//Escalamos al nuevo tamaño del zoom
		if (false && function_exists('imagescale')){
			$img_origen_escalada=imagescale($img_origen,$image_width,$image_height,IMG_BICUBIC_FIXED);
		}else{
			//PHP menor a 5.5 no existe el metodo imagescale
			
			$img_origen_escalada=imagecreatetruecolor($image_width,$image_height);
			
			if ($format=='png'){
				imagealphablending($img_origen_escalada, true);
				$colorTransparent = imagecolorallocatealpha($img_origen_escalada, 0, 0, 0, 127);
				imagefill($img_origen_escalada, 0, 0, $colorTransparent);
				imagesavealpha($img_origen_escalada, true);
			}else{
				$color= UTLImage::hex2rgb($background);
				imagefill($img_origen_escalada, 0, 0, imagecolorallocate($img_origen_escalada, $color[0],$color[1],$color[2]));
			}
			
			$dimensiones_orig=getimagesize($img_path);
			imagecopyresampled($img_origen_escalada,$img_origen,0,0,0,0,$image_width,$image_height,$dimensiones_orig[0],$dimensiones_orig[1]);
		}

		imagedestroy($img_origen);

		//Nueva imagen lienzo
		$img_final=imagecreatetruecolor($crop_width,$crop_height);

		//Incrustamos la porción de imagen original, calculando las cordenadas origen y destino

		$dst_x=0;
		$dst_y=0;

		if (abs($crop_x)!=$crop_x){
			$dst_x=abs($crop_x);
			$crop_width=$crop_width-abs($crop_x);
			$crop_x=0;
		}else{
			$crop_x=abs($crop_x);
			$crop_width=$image_width-$crop_x;
		}

		if (abs($crop_y)!=$crop_y){
			$dst_y=abs($crop_y);
			$crop_y=0;
		}else{
			$crop_y=abs($crop_y);
		}

		$crop_height=$image_height-abs($crop_y);

		if ($crop_height > $image_height){
			$crop_height=$image_height;
		}

		if ($crop_width > $image_width){
			$crop_width=$image_width;
		}

		/*$calculado=array(
			'dst_x'=>$dst_x,
			'dst_y'=>$dst_y,
			'crop_x'=>$crop_x,
			'crop_y'=>$crop_y,
			'crop_width'=>$crop_width,
			'crop_height'=>$crop_height
		);*/

		if ($format=='png'){
			//Rellenamos con Transparencia
			imagealphablending($img_final, true);
			$colorTransparent = imagecolorallocatealpha($img_final, 0, 0, 0, 127);
			imagefill($img_final, 0, 0, $colorTransparent);
			imagesavealpha($img_final, true);
		}else{
			//rellenamos con el color de fondo
			$color= UTLImage::hex2rgb($background);
			imagefill($img_final, 0, 0, imagecolorallocate($img_final, $color[0],$color[1],$color[2]));
		}

		imagecopy($img_final,$img_origen_escalada,$dst_x,$dst_y,$crop_x,$crop_y,$crop_width,$crop_height);

		//Volcamos el resultado

		return $img_final;

	}
	
	/**
	 * 
	 * @param GDImageResource $gdimage
	 * @param string $text
	 * @param int $x in px
	 * @param int $y in px
	 * @param int $font_size in pt
	 * @param string $font_family
	 * @param string $color HEX color (#ffffff)
	 */
	public static function addText($gdimage,$text,$x,$y,$font_size,$font_family,$color='#ffffff',$shadow=false){
		
		$rgb=  UTLImage::hex2rgb($color);
		
		$color_texto = imagecolorallocate($gdimage, $rgb[0], $rgb[1], $rgb[2]);
		
		$font_file=  UTLUtilidades::getFullPath('v4/private/data/fonts/Arialbd.TTF');
		
		switch(strtolower($font_family)){
			case 'times new roman':
			case 'times':
				$font_file=  UTLUtilidades::getFullPath('v4/private/data/fonts/Timesbd.TTF');
				break;
			case 'arial':
				$font_file=  UTLUtilidades::getFullPath('v4/private/data/fonts/Arialbd.TTF');
				break;
			case 'verdana':
				$font_file=  UTLUtilidades::getFullPath('v4/private/data/fonts/Verdanab.TTF');
				break;
		}
		
		//La posición de x-y que nos llega es de la esquina superior izquierda, para pintar el texto calculamos la esquina inferior
		$box=imagettfbbox($font_size,0,$font_file,$text);
		$y=$y+abs($box[5])+6;
				
		if ($shadow){
			$color_shadow = imagecolorallocate($gdimage,128,128,128);
			imagettftext($gdimage, $font_size, 0, $x+2, $y+2, $color_shadow, $font_file, $text);
		}
		
		imagettftext($gdimage, $font_size, 0, $x, $y, $color_texto, $font_file, $text);
		
	}
	
	/**
	 * Agrega el botón simulado de play en el centro de la imágen
	 * @param string $img_path
	 * @return GDImageResource
	 */
	public static function addPlay($img_path){
		
		$img_play=  UTLUtilidades::getFullPath('v4/public/img/UTL/play.png');
		$gd_play=imagecreatefrompng($img_play);
	
		$gd_origen=null;
		
		if (trim($img_path)!=''){
			$extension=strtolower(strrchr($img_path,'.'));

			//imagen original
			switch($extension){
				case '.jpg':
				case '.jpeg':
					$gd_origen=imagecreatefromjpeg($img_path);
					break;
				case '.png':
					$gd_origen=imagecreatefrompng($img_path);
					break;
				case '.gif':
					$gd_origen=imagecreatefromgif($img_path);
					break;
			}
		}
		
		if ($gd_origen) $image_size= getimagesize($img_path);
		$play_size = getimagesize($img_play);
		
		$w=$play_size[0];
		$h=$play_size[1];
		
		$x=round((650-$w)/2);
		$y=round((366-$h)/2);
		
		//Creo un lienzo
		$destino = imagecreatetruecolor(650, 366);
		
		//Inserto la imagen miniatura redimensinandola al tamaño del lienzo
		if ($gd_origen) imagecopyresampled($destino,$gd_origen,0,0,0,0,650,366,$image_size[0],$image_size[1]);
		
		//Inserto el botón play en el centro
		imagecopy($destino,$gd_play,$x,$y,0,0,$w,$h);
		
		return $destino;
		
	}
	
	/**
	 * Return RGB color from HEX color
	 * @param string $hex Color in hex html format: #ffffff or #fff
	 * @return array 0=>R,1=>G,2=>B
	 */
	public static function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		return array($r, $g, $b);
	}
	
}


?>