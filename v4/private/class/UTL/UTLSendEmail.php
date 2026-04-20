<?php
require_once 'v4/private/class/SMTP/SMTPClient.php';
require_once 'v4/private/class/UTL/UTLUtilidades.php';
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/TICK/TICKBase.php';

/**
 * Clase de Utiliades para el envío de emails con multipart
 * con texto plano y html
 * @author Víctor J Chamorro - victor@ipdea.com
 * @package UTL
 * @copyright Ipdea Land, S.L. / Teenvio
 *
 * LGPL v3 - GNU LESSER GENERAL PUBLIC LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU LESSER General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU LESSER General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class UTLSendEmail {
	
	private $finLinea="\r\n";

	/**
	 * @var string
	 */
	private $from='';
	
	/**
	 * @var string
	 */
	private $to;
	
	/**
	 * @var string
	 */
	private $bcc;
	
	/**
	 * @var string
	 */
	private $cc;
	
	/**
	 * @var string
	 */
	private $asunto;
	
	/**
	 * @var string
	 */
	private $replyTo="";
	
	/**
	 * @var string
	 */
	private $cuerpohtml;
	
	/**
	 * @var string
	 */
	private $cuerpoplano;
	
	/**
	 * Codificación: ISO-8869-1 o UTF-8
	 * @var string
	 */
	private $encoding="ISO-8859-1";
	
	/**
	 * Se utilizará un socket SMTP con autorización
	 * @var boolean
	 */
	private $auth=false;
	
	/**
	 * En blanco utiliza la función mail nativa de php, con contenido utiliza SMTP
	 * @var string
	 */
	private $host="127.0.0.1";
	
	/**
	 * @var int
	 */	
	private $port=25;
	
	/**
	 * @var string
	 */
	private $user="";
	
	/**
	 * @var string
	 */
	private $pass="";
	
	/**
	 * @var array
	 */
	private $adjuntos=array();
	
	/**
	 * Almacena errores
	 * @var array
	 */
	private $error=array();

	/**
	 * Develve el asunto codificado en quoted printable listo para ser usado en la cabecera Subject de un email
	 * @param string $asunto
	 * @param bool $utf8 Si es true (por defecto), se enviará indicando que es UTF-8, en caso contrario, ISO-8859-15 pero no realiza la conversión de la cadena
	 */
	static function getAsuntoQuotedPrintable($asunto,$utf8=true){
		return self::getQuotedPrintable($asunto, $utf8);
	}
	
	/**
	 * Develve el texto codificado en quoted printable listo para ser usado en una cabecera de mail
	 * @param string $texto
	 * @param bool $utf8 Si es true (por defecto), se enviará indicando que es UTF-8, en caso contrario, ISO-8859-15 pero no realiza la conversión de la cadena
	 */
	static function getQuotedPrintable($texto,$utf8=true){
		if ($utf8){
			return '=?utf-8?Q?'.str_replace(".", "=2E", str_replace ("%","=",rawurlencode($texto))).'?=';
		}else{
			return '=?iso-8859-15?Q?'.str_replace(".", "=2E", str_replace ("%","=",rawurlencode($texto))).'?=';
		}
	}
	
	/**
	 * 
	 * @param string $quoted_printable_mime_text
	 * @return string
	 */
	static function decodeMimeStr($string,$charset='utf-8'){
		$newString = '';
		
		if (function_exists('imap_mime_header_decode')){
		
			$elements = imap_mime_header_decode($string);
			for($i = 0; $i < count($elements); $i++) {
				if($elements[$i]->charset == 'default') {
					$elements[$i]->charset = 'iso-8859-1';
				}
				$newString .= iconv(strtoupper($elements[$i]->charset), $charset.'//TRANSLIT', $elements[$i]->text);
			}
		}else{
			try{
				throw new TeException('No esta instalada la librería IMAP en php, utilizando metodo alternativo para "imap_mime_header_decode"', __LINE__,__CLASS__);
			} catch (TeException $ex) {}
			
			$header=str_ireplace(array('?= =?','?B?=','?Q?='),array('?==?','?B? =','?Q? ='),$string);
			
			$newString=$header;

			//Buscamos las partes codificadas entre =? y ?=
			$pos=0;

			while (($pos=strpos($header, '=?', $pos)) !== false ){

				$pos+=2;
				$pos2 = strpos($header, '?=', $pos);


				if ($pos2){
					$texto='=?'.substr($header, $pos, $pos2-$pos).'?=';

					$partes=explode('?', $texto);
					if (count($partes)==5){
						$fromcharset=$partes[1];
						$encoding=$partes[2];
						$text=$partes[3];

						switch(strtoupper($encoding)){
							case 'Q':
								$text=quoted_printable_decode(str_replace('_',' ',$text));
								break;
							case 'B':
								$text=base64_decode($text);
								break;
						}

						$tmptext=iconv($fromcharset, $charset.'//TRANSLIT', $text);
						if ($tmptext!==false) $text=$tmptext;
						$newString=str_replace($texto,$text,$newString);

					}


				}
			}
			
		}
                return $newString;
	}
	
	/**
	 * Codifca el texto pasado en quoted printable.
	 * No codifica los saltos de linea
	 * @param type $text
	 * @return string
	 */
	static function quotedPrintableEmailEncode($text){
		
		//return quoted_printable_encode($text);
		//return str_replace('=0A',"\n",quoted_printable_encode($text));
		
		$array=explode("\n", str_replace("\r\n","\n",$text) );
		
		$textreturn='';
		
		/**
		 * El estandar dicta como máximo 75 caracteres, pero nos guardamos 3 de reserva por si tenemos 
		 */
		$maxlen=75-3;
			
		
		foreach($array as $line){
			//$qp_line=str_replace(array("=\n","=\r\n",'=0A','=09'), array('','',"\n","\t"),quoted_printable_encode($line));
			$qp_line=str_replace(array("=\n","=\r\n",'=0A'), array('','',"\n"),quoted_printable_encode($line));
			
			//partimos en $maxlen caracteres
			$split=str_split($qp_line,$maxlen);
			for($i=0;$i<count($split);$i++){
					//Si al cortar en $maxlen rompemos una secuencia de quoted printable (=3D se tranformaría en =3=\nD) retrasamos el corte
					//y agregamos la diferencia en la siguiente linea
					if (substr($split[$i],$maxlen-1,1)=='='){
						$split[$i+1]=  substr($split[$i], $maxlen-1).$split[$i+1];
						$split[$i]=substr($split[$i],0,$maxlen-1);
					}elseif(substr($split[$i],$maxlen-2,1)=='='){
						$split[$i+1]=  substr($split[$i], $maxlen-2).$split[$i+1];
						$split[$i]=substr($split[$i],0,$maxlen-2);
					}
					
					$textreturn.=$split[$i]."=\n";
			}
			
			$textreturn=substr($textreturn,0,-2)."\n";
		}
		return substr($textreturn,0,-1);
		
	}
	
	/**
	 * Constructor de clase para el envío de Emails con multipart
	 * @param string $asunto
	 * @param string $cuerpohtml
	 * @param string $cuerpoplano
	 * @param string $from
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 */
	public function __construct($asunto="", $cuerpohtml = "", $cuerpoplano = "", $from = "", $to = "", $cc = "", $bcc = "",$replyto = "") {
		
		if (!UTLUtilidades::isCLI()){		
			if (substr($_SERVER['SERVER_ADDR'],0,10)=='192.168.1.' || (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR'] )){
				//Estamos en entorno local, usamos el smtp de gmail
				
				$ticketsBase= new TICKBase();
				
				$this->host='tls://smtp.gmail.com';
				$this->port=465;
				$this->user=$ticketsBase->getImapEmail();
				$this->pass=$ticketsBase->getImapPass();
			}
		}
		
		$this->asunto = trim($asunto);
		$this->cuerpohtml = $cuerpohtml;
		$this->cuerpoplano = $cuerpoplano;
		$this->from = trim($from);
		$this->to = trim($to);
		$this->cc = trim($cc);
		$this->bcc = trim($bcc);
		$this->replyTo= trim($replyto);
	}
	
	public function setReplyTo($email){
		$this->replyTo=trim($email);
	}
	
	/**
	 * Establece si se utilizará o no autorización.
	 * Si se marca a true, se utilizará una conexión SMTP mediante socket
	 * por lo que debe esar completado los datos host,port, user y pass
	 * @param boolean $bool 
	 */
	public function setAutorizacion($bool){
		$this->auth=(bool) $bool;
	}
	
	/**
	 * Inicializa el contenido de la parte HTML
	 * @param string $cuerpo
	 */
	public function setCuerpoHTML($cuerpo){
		$this->cuerpohtml=$cuerpo;		
	}
	
	/**
	 * Inicializa el conenido de la parte de texto plano
	 * @param string $cuerpo
	 */
	public function setCuerpoPlano($cuerpo){
		$this->cuerpoplano=$cuerpo;
	}
	
	/**
	 * Establece el asunto del email, si se pasa un encode el asunto se codificará en quoted printable
	 * @param string $subject deprecated 
	 */
	public function setAsunto($subject,$encode=''){
		$this->asunto=trim($subject);
	}
	
	/**
	 * Indica el valor de la cabecera FROM (desde) del email
	 * se puede emplear el formato 'nombre <usuario@dominio>'
	 * 'nombre' debe ir codificado en quoted printable
	 * @see UTLSendEmail::getAsuntoQuotedPrintable()
	 * @param string $from	 
	 */
	public function setFrom($from){
		$this->from=trim($from);
	}
	
	/**
	 * Indica el valor de la cabecera TO (para)
	 * se puede emplear el formato 'nombre <usuario@dominio>'
	 * 'nombre' debe ir codificado en quoted printable
	 * @param string $to
	 */
	public function setTo($to){
		$this->to=trim($to);
	}
	
	/**
	 * Indica el valor de la cabecera CC (con copia a)
	 * se puede emplear el formato 'nombre <usuario@dominio>'
	 * 'nombre' debe ir codificado en quoted printable
	 * @see UTLSendEmail::getAsuntoQuotedPrintable()
	 * @param string $cc
	 */
	public function setCC($cc){
		$this->cc=trim($cc);
	}
	
	/**
	 * Indica el valor de la cabecera BCC (con copia oculta a)
	 * se puede emplear el formato 'nombre <usuario@dominio>'
	 * 'nombre' debe ir codificado en quoted printable
	 * @see UTLSendEmail::getAsuntoQuotedPrintable()
	 * @param string $bcc
	 */
	public function setBCC($bcc){
		$this->bcc=trim($bcc);
	}
	
	/**
	 * Establece la codificación de las partes tanto HTML como plano a ISO-8859-1	 
	 */
	public function setEncodingLATIN1(){
		$this->encoding="ISO-8859-1";
	}
	
	/**
	 * Establece la codificación de las partes tanto HTML como plano a ISO-8859-1	 
	 */
	public function setEncodingISO(){
		$this->setEncodingLATIN1();
	}
	
	/**
	* Establece la codificación de las partes tanto HTML como plano a UTF-8
	*/
	public function setEncodingUTF8(){
		$this->encoding="UTF-8";
	}
	
	/**
	* Retorna el cuerpo HTML
	* @return string
	*/
	private function getCuerpoHTML(){
		
		#Limpiamos saltos de lineas y lineas mas largas de 990 caracteres
		$cuerpo= $this->cuerpohtml;
		$cuerpo= str_replace("\r\n","\n",$cuerpo);
		$cupero= str_replace("\r","",$cuerpo);
		$cuerpo= wordwrap($cuerpo,990,"\n",true);

		return $cuerpo;			
	}

	/**
	* Retorna el cuerpo Plano 
	* @return string
	*/
	private function getCuerpoPlano(){

		#Limpiamos saltos de lineas y lineas mas largas de 990 caracteres
		$cuerpo= $this->cuerpoplano;
		$cuerpo= str_replace("\r\n","\n",$cuerpo);
		$cupero= str_replace("\r","",$cuerpo);
		$cuerpo= wordwrap($cuerpo,990,"\n",true);

		return $cuerpo;			
	}
	
	/**
	 * Retorna un array con todos los errores ocurridos
	 * @return array
	 */
	public function getErrors(){
		return $this->error;
	}
	
	/**
	 * Retorna la desripción del último error
	 * @return string
	 */
	public function getLastError(){
		return (isset($this->error[0]) ? $this->error[0] : '');
	}
	
	/**
	 * @param string $nombre
	 * @param string $contenido
	 * @param string $contentType
	 */
	public function addAdjunto($nombre="",$contenido="",$contentType="application/octet-stream"){
		if ($nombre!="" && $contenido!=""){
			$this->adjuntos[$nombre]=array($contenido,$contentType);
		}else{
			throw new TeException('No hay contenido o nombre en el adjunto a agregar',__LINE__,__CLASS__);
		}
	}

	/**
	 * Envía el email con los datos establecidos
	 * Devuelve true o false en función de lo que devuelva la función mail de php
	 * @param string $custom_cabs Cabecera personalizada (opcional)
	 * @return boolean
	 */
	public function send($custom_cabs="") {
		
		$finLinea=$this->finLinea;
		
		# -=-=-=- MIME BOUNDARY
		$mime_boundary="teenvio_".md5(time());
		$mime_boundary_mixed="";
		

		$cabeceras = "From: ".$this->from . "$finLinea";
		
		if (!empty($this->bcc) && $this->auth==false)
			$cabeceras.= "Bcc: ".$this->bcc . "$finLinea";
		if (!empty($this->cc))
			$cabeceras.= "Cc: ".$this->cc . "$finLinea";
		if ($this->replyTo!=""){
			$cabeceras .= "Reply-To: ".$this->replyTo."$finLinea";
		}
		
		if (trim($custom_cabs)!=''){
			$cabeceras .= trim($custom_cabs);
			$cabeceras .= $finLinea;
		}
		$cabeceras .= "MIME-Version: 1.0$finLinea";
		
		$cabecera_plano="";
		
		if (count($this->adjuntos)){
			//Hay adjuntos, genero un multipar-mixed por encima del multipart-alternative
			$mime_boundary_mixed="teenvio_".md5(time().'adjuntos');			
			$cabeceras .= "Content-Type: multipart/mixed;$finLinea      boundary=\"$mime_boundary_mixed\"$finLinea";
			$cabecera_plano .= "$finLinea--$mime_boundary_mixed$finLinea";
			$cabecera_plano .= "Content-Type: multipart/alternative;$finLinea      boundary=\"$mime_boundary\"$finLinea";
		}else{
			$cabeceras .= "Content-Type: multipart/alternative;$finLinea      boundary=\"$mime_boundary\"$finLinea";
		}

		$cabecera_html = "$finLinea--$mime_boundary$finLinea";
		$cabecera_html .= "Content-Type: text/html;$finLinea      charset=".$this->encoding."$finLinea";
		$cabecera_html .= "Content-Transfer-Encoding: 8bit$finLinea";

		$cabecera_plano .= "$finLinea--$mime_boundary$finLinea";
		$cabecera_plano .= "Content-Type: text/plain;$finLinea      charset=\"".$this->encoding."$finLinea";
		$cabecera_plano .= "Content-Transfer-Encoding: 8bit$finLinea";

		$html = $cabecera_html ."$finLinea" . $this->getCuerpoHTML();
		$plano = $cabecera_plano . "$finLinea" . $this->getCuerpoPlano();
		$body = $plano ."$finLinea". $html . "$finLinea$finLinea";
		
		$body.="$finLinea--" . $mime_boundary . "--$finLinea";
		
		$sAdjuntos="";
		foreach($this->adjuntos as $nombre=>$contenido){
			
			$data=$contenido[0];
			if (is_file($contenido[0])){
				$data=@file_get_contents($contenido[0]);
			}
			if(strlen($data)>0){
				$quoted_name=self::getQuotedPrintable($nombre);
				$sAdjuntos .= "$finLinea--$mime_boundary_mixed$finLinea";
				$sAdjuntos .= 'Content-Type: '.$contenido[1].';name="'.$quoted_name.'"'."$finLinea";
				$sAdjuntos .= "Content-Transfer-Encoding: base64$finLinea";
				$sAdjuntos .= 'Content-Disposition: attachment;filename="'.$quoted_name."\"$finLinea$finLinea";
				$sAdjuntos .= chunk_split(base64_encode($data))."$finLinea$finLinea";
			}else{
				if(UTLUtilidades::isDebug()){
					UTLUtilidades::echoDebug('No se ha podido agregar el adjunto '.$nombre.' no se ha podido obtener el contenido', __CLASS__);
				}
			}
		}
		
		$body.=$sAdjuntos;
		if (count($this->adjuntos)){
			$body.="--" . $mime_boundary_mixed . "--$finLinea";
		}
		
		$asunto= UTLSendEmail::getAsuntoQuotedPrintable($this->asunto,($this->encoding=="UTF-8"));
				
		if (trim($this->host)==''){
			return @mail($this->to, $asunto, $body, $cabeceras);
		}else{
			return $this->mail_auth($asunto, $body, $cabeceras);
		}
	}

	/**
	 * Envía mail mediante smtp
	 * @param string $to
	 * @param string $asunto
	 * @param string $body
	 * @param string $cabeceras 
	 * @return boolean
	 */
	private function mail_auth($asunto,$body,$cabeceras){
		
		$finLinea=$this->finLinea;
		
		$headers=explode("$finLinea",'To: '.$this->to.$finLinea.'Subject: '.$asunto.$finLinea.$cabeceras);
		
		if(trim($this->from)==''){
			foreach($headers as &$head){
				if(substr(trim($head),0,5)=='From:'){
					$this->from=trim(substr(trim($head),5));
				}
				if(substr(trim($head),0,4)=='Bcc:'){
					$this->bcc=trim(substr(trim($head),4));
					$head="";
				}
				if(substr(trim($head),0,3)=='Cc:'){
					$this->cc=trim(substr(trim($head),3));
				}
			}
		}
		
		$params=array(
		    'host'=>$this->host,
		    'port'=>$this->port,
		    'helo'=>'teenvio.com',
		    'auth'=>(trim($this->user)!=''),
		    'user'=>$this->user,
		    'pass'=>$this->pass,
		    'timeout'=>5
		);
		
		$objSMTP=new SMTPClient($params);
		if ($objSMTP->connect()){
				
			$recipients=array();

			$mailto=explode(',',$this->to);
			foreach($mailto as $to){ $recipients[]=$this->limpiaEmail($to); }

			if (!empty($this->bcc)){
				$mailbcc=explode(',',$this->bcc);
				foreach ($mailbcc as $bcc) $recipients[]=$this->limpiaEmail($bcc);
			}
			if (!empty($this->cc)){
				$mailcc=explode(',',$this->cc);
				foreach($mailcc as $cc) $recipients[]=$this->limpiaEmail($cc);
			}

			$sendParams=array(
			    'recipients'=>$recipients,
			    'from'=>$this->limpiaEmail($this->from),
			    'headers'=>$headers,
			    'body'=>$body
			);

			$objSMTP->send($sendParams);
		}
		
		if (UTLUtilidades::getDebugLevel()>1 && count($objSMTP->errors)>0){
			
			print_r($objSMTP->errors);
			echo "\n<br/>";
			print_r($objSMTP);
		}
		if (UTLUtilidades::getDebugLevel()>4){
			print_r($sendParams);
			echo "\n<br/>";
			print_r($objSMTP);
		}
		
		if (count($objSMTP->errors)>0){
			$this->error = $objSMTP->errors;
			try{
				throw new TeException(implode("\n",$this->error), 500,__CLASS__);
			} catch (TeException $ex) {}
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * Limpia un email compuesto por nombre <email@dominio>, devolviendo únicamente el email@dominio
	 * @param string $email
	 * @param string $texto Se rellenará con la parte de texto de la direccion
	 * @return string 
	 */
	public function limpiaEmail($email,&$texto=null){
		
		if(strpos($email, '<')!==false && strpos($email, '>')!==false){
			$texto=substr($email,0,strpos($email, '<'));
			$email=substr($email,strpos($email, '<')+1);
			$email=substr($email,0,strpos($email, '>'));
		}
		
		return trim($email);
	}
	
	/**
	 * Establece el host para el servidor SMTP con autorización
	 * Ejemplos: tls://smtp.gmail.com, localhost, smtp.dominio.com
	 * @param string $host 
	 */
	public function setHost($host){
		$this->host=$host;
	}
	
	/**
	 * Establece el puerto para el servidor SMTP con autorización
	 * Ejemplos: 25, 465
	 * @param int $port 
	 */
	public function setPort($port){
		$this->port=$port;
	}
	/**
	 * Establece el usuario para el servidor SMTP con autorización
	 * @param string $user 
	 */
	public function setUser($user){
		$this->user=$user;
	}
	
	/**
	 * Establece la contraseña para el servidor SMTP con autorización
	 * @param string $pass 
	 */
	public function setPass($pass){
		$this->pass=$pass;
	}
	
	/**
	 * Envía un email.
	 * La síntaxis es la misma que la función mail de php y está a modo de compatibilidad con esta.
	 * Solo se recomienda para mails de avisos internos, para mails públicos o complecos (bcc, html+plano, adjuntos,etc) se recomienda utilizar
	 * los métodos de esta clase y no este método de compatibilidad.
	 * 
	 * @param string $to
	 * @param string $subject
	 * @param string $content
	 * @param string $headers
	 */
	static function mail($to,$subject,$content,$headers=''){
		$obj=new self();
		if (trim($obj->host)==''){
			return @mail($to,$subject,$content,$headers);
		}else{
			$obj->setTo($to);
			$obj->mail_auth($subject, $content, $headers);
		}
	}
}
?>
