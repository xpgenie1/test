<style type="text/css">
	.row-fluid .span4{ margin-left:0px;}
	.row-fluid .span4 label{ cursor:auto;}
	.row-fluid .span4 input{ float:left;}
	.icono_personalizar{ background: url("/v4/public/img/RSO/codigo-corto.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0); float: left; height: 22px; margin:4px 0 0 5px; width: 22px;}
	.icono_personalizar .capatooltip{ background: none repeat scroll 0 0 white; border: 4px solid #cccccc; border-radius: 10px; display: none; margin-left: -240px; padding: 20px; position: absolute; width: 212px;z-index: 20;}
	.icono_personalizar:hover .capatooltip{ display:block; font-size:14px;white-space:normal;}
	.icono_personalizar .capatooltip strong{ display:block;margin-bottom:10px;}
	.icono_personalizar .capatooltip .help{ margin-top:20px;float:right}
	#tauxiliares label{ font-weight: bold}
	input.number{ text-align:right;}
	
	.parrafo .span6 i:hover{ color:#387599; }
	.parrafo .span6 i { color: #666666;font-size: 80px;cursor:pointer; }
	.parrafo .contenido{ display:none; }
	.total{ margin-top:20px; }
	.btn-xs{ background-color:#387599;}
	.btn-xs:hover{ background-color:#fff;color:#387599;border-color:#ccc;}
	.btnApi{ cursor:pointer;}
	.btnApi.disable{ opacity:0.5;}
	.btnApi.lead{ cursor:default;}
	h3{ margin:20px 0;}
	.select-hide .btn-select{ border-radius:0px !important;}
</style>

<script type="text/javascript">
		if (jQuery){ jQuery(document).ready(function(){
			if (jQuery.urlParamAjax('section')){
				var section=jQuery.urlParamAjax('section');
				jQuery('li a[href=#'+section+']').click();
			}
			
			jQuery('.parrafo', this).click(function () {

				jQuery('.btnApi').children().css('color', '#666666');// colores a original
				jQuery('.btnApi').removeClass('lead').addClass('disable');//clase a original

				jQuery('.total').empty().append(jQuery('.contenido', this).clone());//sobre el contenedor, clono
				jQuery('.btnApi', this).children().css('color', '#387599');
				jQuery('.btnApi', this).addClass('lead').removeClass('disable');
			});
			
			jQuery('select').selectCamuflado();
		});}
	
	
</script>

<div class="alert alert-warning inline">
	<button data-dismiss="alert" class="close" type="button">×</button>
	<strong><i class="icon-warning-sign"></i> __#Aviso:#__</strong> __#La configuración de esta sección afecta de forma general a todo el plan#__
</div>

<div class="page">
	<ul class="nav nav-tabs">
		<!-- @ BLOQUE_HERRAMIENTAS @ -->
		<li class="active"><a href="#tauxiliares" data-toggle="tab"><i class="icon-th-list"></i> __#Campos Auxiliares#__</a></li>
		<li class=""><a href="#tgenerales" data-toggle="tab"><i class="icon-th-list"></i> __#Campos Generales#__</a></li>
		<li class=""><a href="#tbaja" data-toggle="tab"><i class="icon-cogs"></i> __#Sistema de Bajas#__</a></li>
		<li class=""><a href="#tavisos" data-toggle="tab"><i class="icon-exclamation-sign"></i> __#Avisos#__</a></li>
		<!-- @ BLOQUE_HERRAMIENTAS @ -->
                <li class=""><a href="#tapi" data-toggle="tab"><i class="icon-puzzle-piece"></i> __#APIs#__</a></li>
	</ul>
	
	<div class="tab-content">
		<div class="tab-pane fade in active" id="tauxiliares">
			
			<div class="row-fluid">
				<!-- @ BLOQUE_AUXILIARES @ -->
				<div class="span4">
					<label><!-- {CAMPO_ALIAS} --></label>
					<input type="text" name="<!-- {CAMPO} -->" value="<!-- {VALOR} -->" />
					<div class="icono_personalizar">
						<div class="capatooltip">
							<strong>__#Código corto personalización#__ </strong>
							<div class="code"> ###<!-- {CAMPO} -->### </div>
						</div>
					</div>
				</div>
				<!-- @ BLOQUE_AUXILIARES @ -->
			</div>
			<div class="row-fluid">
				<button id="btnGuardar" class="btn pull-right btn-success siguiente"><i class="icon-cog"></i> __#Guardar#__ </button>
			</div>
		</div>
						
		<div class="tab-pane fade" id="tgenerales">
			<div class="row-fluid">
				<!-- @ BLOQUE_GENERAL @ -->
				<div class="span4">
					<label><!-- {VALOR} --></label>
					<input readonly="readonly" type="text" name="<!-- {CAMPO} -->" value="<!-- {VALOR} -->" />
					<div class="icono_personalizar">
						<div class="capatooltip">
							<strong>__#Código corto personalización#__ </strong>
							<div class="code"> ###<!-- {CAMPO} -->### </div>
						</div>
					</div>
				</div>
				<!-- @ BLOQUE_GENERAL @ -->
			</div>
									
			<!-- @ BLOQUE_CLAVE_PROPIA @ -->			
			<hr style="margin-top:20px;">
			<div class="row-fluid">
				<div class="span12">
					<p>__#El campo identificador se puede usar para uso interno de cada cliente, permitiendo la búsqueda y listado de contactos.#__</p>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label><!-- {CAMPO_CLAVE_PROPIA} --></label>
					<input type="text" name="dato_99" value="<!-- {VALOR_CLAVE_PROPIA} -->" />
					<div class="icono_personalizar">
						<div class="capatooltip">
							<strong>__#Código corto personalización#__ </strong>
							<div class="code"> ###dato_99### </div>
						</div>
					</div>
				</div>
				<div class="span4" style="clear: both;">
                                        <div class="ui-flipswitch long ui-shadow-inset ui-bar-inherit ui-corner-all <!-- {CLAVE_PROPIA_CHECKED} --> long">
						<a href="#" class="ui-flipswitch-on long ui-btn ui-shadow ui-btn-inherit">__#Mostrar#__</a><span class="ui-flipswitch-off">__#Ocultar#__</span>
						<input type="checkbox" id="flip-checkbox-clave-propia" name="clave_propia" data-role="flipswitch" class="ui-flipswitch-input" tabindex="-1" <!-- {CLAVE_PROPIA_VALUE_CHECKED} --> >
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<button id="btnGuardarGeneral" class="btn pull-right btn-success siguiente"><i class="icon-cog"></i> __#Guardar#__ </button>
			</div>
			<!-- @ BLOQUE_CLAVE_PROPIA @ -->
						
			
		</div>
						
		<div class="tab-pane fade" id="tbaja">
			<div class="row-fluid">
				<div class="span4">
					<div class="control-group input-append">
						<label for="url_baja" class="control-label">__#Redirección tras la baja#__</label>

						<div class="controls">
							<input placeholder="http://" type="text" id="url_baja" name="url_baja" value="<!-- {URL_BAJA} -->" />
							<button data-original-title="__#Dirección web donde será enviado el contacto una vez ha cursado baja. Se puede utilizar par mostrar un mensaje de agradecimiento o confirmación de la baja. Afecta de forma instantánea a todas las nuevas bajas.#__" class="btn btn-help"><i class="icon-question"></i></button>
						</div>
					</div>
							
					<div class="control-group input-append">
						<label for="email_baja" class="control-label">__#Email para aviso de bajas manuales#__</label>

						<div class="controls">
							<input placeholder="email" type="text" id="email_baja" name="email_baja" value="<!-- {EMAIL_BAJA} -->" />
							<button data-original-title="__#Dirección de email donde será enviado un email cada vez que un contacto se da de baja manualmente, desde el pié de los envíos o la url de baja. Afecta de forma instantánea a todas las nuevas bajas.#__" class="btn btn-help"><i class="icon-question"></i></button>
						</div>
					</div>
				</div>
				<div class="span4">
					
					<div class="control-group input-append">
						<label for="url_baja" class="control-label">__#Rebotes para baja#__</label>

						<div class="controls">
							<input placeholder="0" disabled="disabled" type="text" class="number" id="num_rebotes" name="num_rebotes" value="<!-- {NUM_REBOTES} -->" />
							<button data-original-title="__#Número de veces que un contacto tiene que generar rebotes consecutivos para procesarse como baja. Cero = Desactivado#__" class="btn btn-help"><i class="icon-question"></i></button>
						</div>
					</div>
					
					<div class="control-group input-append">
						<label>__#Comodines avanzados#__</label>
						<input readonly="readonly" type="text" name="str" value="__#Comodines avanzados#__" />
						<div class="icono_personalizar">
							<div class="capatooltip">
								<strong>__#Comodines avanzados para utilizar dentro de un enlace (href) para enlaces de baja#__ </strong>
								<div class="code">- __#Página de baja que solicita confirmación:#__</div>
								<div class="code">###url_unsubscribe###</div><br/>
								<div class="code">- __#Página de baja sin confirmación, genera baja directa:#__</div>
								<div class="code">###url_direct_unsubscribe###</div>
							</div>
						</div>
					</div>
				</div>
				
			</div>
			<!-- @ BLOQUE_TEENVIO @ -->				
			<p>__#Consulta la documentación para notifiaciones automáticas (api):#__</p>
			<a href="https://github.com/teenvio/documentacion/raw/master/Bajas/Notificaci%C3%B3n%20de%20bajas%20manuales%20por%20URL.pdf" target="_blank" class="btn btn-primary btn-xs">__#Notificaciones automáticas de bajas#__</a>
                        <!-- @ BLOQUE_TEENVIO @ -->
                        <br><br>
			
			<div class="row-fluid">
				<button id="btnGuardarBaja" class="btn pull-right btn-success siguiente"><i class="icon-cog"></i> __#Guardar#__ </button>
			</div>
			
		</div>
		
		<div class="tab-pane fade" id="tavisos">
			<form id="favisos" onsubmit="return false;">
				<div class="row-fluid">
					<div class="span4">
						<div class="control-group input-append">
							<label for="porcentaje_aviso_contactos" class="control-label">__#Avisar del consumo de contactos mayor del#__</label>
							<div class="controls">
								<input type="text" maxlength="2" name="porcentaje_aviso_contactos" value="<!-- {PORCENTAJE_CONTACTOS} -->" class="number" />
								<span title="__#Porcentaje#__" class="clear add-on">%</span>
								<button data-original-title="__#Porcentaje tras el cual será enviado un correo electrónico de aviso.#__" class="btn btn-help"><i class="icon-question"></i></button>
							</div>
						</div>

					</div>
					<div class="span4">
						<div class="control-group input-append">
							<label for="porcentaje_aviso_envios" class="control-label">__#Avisar de consumo de envíos mayor del#__</label>
							<div class="controls">
								<input type="text" maxlength="2" name="porcentaje_aviso_envios" value="<!-- {PORCENTAJE_ENVIOS} -->" class="number" />
								<span title="__#Porcentaje#__" class="clear add-on">%</span>
								<button data-original-title="__#Porcentaje tras el cual será enviado un correo electrónico de aviso.#__" class="btn btn-help"><i class="icon-question"></i></button>
							</div>
						</div>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span4">
						<div class="control-group input-append">
							<label for="porcentaje_aviso_contactos" class="control-label">__#Idioma preferente#__</label>
							<div class="controls">
								<!-- {DDL_IDIOMAS_AVISOS} -->
								<span title="__#Idioma#__" class="clear add-on"><i class="icon-flag"></i></span>
								<button data-original-title="__#Idioma a utilizar para avisos o emails lanzados automáticamente.#__" class="btn btn-help"><i class="icon-question"></i></button>
							</div>
						</div>

					</div>
				</div>
			</form>
			<div class="row-fluid">
				<button id="btnGuardarAvisos" class="btn pull-right btn-success siguiente"><i class="icon-cog"></i> __#Guardar#__ </button>
			</div>
		</div>
								
		<!-- AQUI LA SOLUCIÓN 2-->
		<div class="tab-pane fade" id="tapi">

			<div class="paso" id="tipo">

				<div class="row-fluid">
					<!--REST-->
					<div id="p3" class="parrafo" style="">
						<div class="span4">
							<center>
								<a  id="btnSoap" class="btnApi">
									<i class="icon-retweet icon-3x"></i><!--icon-group-->
									<p>REST - OpenAPI 3</p>
								</a>
							</center>
						</div>
						<div id="contenidoSoap" class="contenido">
							<h3>__#Descripción:#__</h3>
							<p>__#El API más potente de %%. Utilizando la tecnología||<!-- {COMERCIAL_NOMBRE} -->#__<strong> REST </strong>__#ampliamente extendida y aplicando la especificación estándar#__<strong> OpenAPI 3 </strong>__#para garantizar una integración sencilla con cualquier desarrollo. Todas las acciones, parámetros, filtros y respuestas pueden verse y probarse en nuestro visor online.#__</p>
							<h3>__#Datos de conexión:#__</h3>
							<table class="table table-bordered">
								<tbody>
									<tr>
										<td><strong>swagger.json</strong></td>
										<td><!-- {HOST_POSTSOAPAPI} -->v4/public/api/rest/v2/swagger.json</td>
									</tr>
									<tr>
										<td><strong>__#Server#__</strong></td>
										<td><!-- {HOST_POSTSOAPAPI} -->v4/public/api/rest/v2</td>
									</tr>
								</tbody>
							</table>
                                                        <!-- @ BLOQUE_API_REST_DOC @ -->                
							<h3>__#Documentación:#__</h3>
							<p>__#Consulta la documentación y realiza las pruebas en:#__</p>
							<p>
								<a class="btn btn-primary btn-xs" href="<!-- {URL_DOC_API_REST} -->" title="<!-- {COMERCIAL_NOMBRE} -->/REST-API" target="_blank">__#Documentación API REST#__</a>
							</p>
                                                        <!-- @ BLOQUE_API_REST_DOC @ -->
						</div>
					</div>
					<!--SMPT-->
					<div id="p1" class="parrafo">
						<div class="span4">
							<center>
								<a id="btnSmtp" class="btnApi">
									<i class="icon-envelope-alt icon-3x"></i>
									<p>SMTP - API</p>
								</a>
							</center>
						</div>
						<div class="contenido">
							<h3>__#Descripción:#__</h3>
								<p>__#Nuestra API SMTP permite realizar campañas de email marketing directamente utilizando el protocolo estándar SMTP, para poder realizar envíos desde todos los gestores de correo más habituales cómo Outlook, Thunderbird, Mail Apple, etc.#__</p>
								<p>__#Si tienes conocimiento en desarrollo también puedes integrar tus campañas de email marketing a través del protocolo SMTP en programaciones propias, plataformas open-source , e-commerce, CMS, ERPS, etc.#__</p>
								<p>__#En la documentación se explican las particularidades del servicio SMTP.#__</p>
							
							<h3>__#Datos de conexión:#__</h3>
							<table class="table table-bordered">
								<tbody>
									<tr>
										<th></th>
										<th><strong>__#Servidor A#__</strong></th>
										<th><strong>__#Servidor B#__</strong></th>
									</tr>
									<tr>
										<td><strong>__#Puerto TCP#__</strong></td>
										<td>58700 / 2500</td>
										<td>46500</td>
									</tr>
									<tr>
										<td><strong>Host</strong></td>
										<td><!-- {HOST_SMTPAPI} --></td>
										<td><!-- {HOST_SMTPAPI} --></td>
									</tr>
									<tr>
										<td><strong>__#Cifrado inicial#__</strong></td>
										<td>-</td>
										<td>TLSv1.2</td>
									</tr>
									<tr>
										<td><strong>__#Cifrado bajo demanda#__</strong></td>
										<td>__#Si#__ (STARTTLS TLSv1.2)</td>
										<td>-</td>
									</tr>
									<tr>
										<td><strong>__#Tamaño máximo mensaje#__</strong></td>
										<td>500kb</td>
										<td>500kb</td>
									</tr>
									<tr>
										<td><strong>__#Requiere login#__</strong></td>
										<td>__#Si#__</td>
										<td>__#Si#__</td>
									</tr>
									<tr>
										<th><strong>__#Método para el login#__</strong></th>
										<td>AUTH LOGIN</td>
										<td>AUTH LOGIN</td>
									</tr>
									<tr>
										<th><strong>__#Usuario#__</strong></th>
										<td><a href="/v4/public/usuarios/listado.php">[__#nombre de usuario#__]</a>.<!-- {PLAN} --></td>
										<td><a href="/v4/public/usuarios/listado.php">[__#nombre de usuario#__]</a>.<!-- {PLAN} --></td>
									</tr>
								</tbody>
							</table>
                                                        <!-- @ BLOQUE_API_SMTP_DOC @ -->  
							<h3>__#Documentación:#__</h3>
							<p>__#Consulta la documentación y los ejemplos de código:#__</p>
							<p>
                                                                <!-- {BTN_DOC} -->
                                                                <!-- {BTN_EJE} -->
							</p>
                                                        <!-- @ BLOQUE_API_SMTP_DOC @ -->  
						</div>
					</div>
					<!--POST-->
					<div id="p2" class="parrafo">
						<div class="span4">
							<center>
								<a id="btnPost" class="btnApi">
									<i class="icon-code icon-3x"></i><!--con-external-link-->
									<p>POST - API</p>
								</a>
							</center>
						</div>
						<div id="contenidoPost" class="contenido">
							<h3>__#Descripción:#__</h3>
								<p>__#El POST-API es una potente api donde es posible añadir, modificar, agrupar, desagrupar, dar de baja, lanzar envíos, obtener estadísticas, etc.#__</p>
								<p>__#Utiliza peticiones simples HTTP POST para realizar las acciones, por lo que resulta muy práctica para interactuar entre un desarrollo propio y su cuenta de %%.||<!-- {COMERCIAL_NOMBRE} -->#__</p>
								<p>__#Consulta la documentación para ver todos los métodos que ofrece.#__</p>
							<h3>__#Datos de conexión:#__</h3>
							<table class="table table-bordered">
								<tbody>
									<tr>
										<td><strong>__#URL Base#__</strong></td>
										<td><!-- {HOST_POSTSOAPAPI} -->v4/public/api/post/</td>
									</tr>
									<tr>
										<td><strong>__#Peticiones#__</strong></td>
										<td>__#Se permiten peticiones GET y POST, pero POST es recomendado#__</td>
									</tr>
									<tr>
										<td><strong>__#Respuestas#__</strong></td>
										<td>__#HTTP de texto plano en UTF-8 (Content-Type: text/plan; charset=UTF-8) con el código de respuesta "HTTP/1.1 200 OK" en caso de petición correcta ó "HTTP/1 400 Bad Request" si hay fallo.#__</td>
									</tr>
									<tr>
										<td><strong>__#Texto de las respuestas#__</strong></td>
										<td>__#Irá siempre precedido de "OK:" o "KO:" en función del resultado de la petición.#__</td>
									</tr>
									<tr>
										<th><strong>__#En caso de error#__</strong></th>
										<td>__#El texto de respuesta devolverá "KO:" maś un código de error y una descripción.#__</td>
									</tr>
									<tr>
										<th><strong>__#Credenciales#__</strong></th>
										<td>user: <a href="/v4/public/usuarios/listado.php">[__#nombre de usuario#__]</a>, plan: <!-- {PLAN} --></td>
									</tr>
								</tbody>
							</table>
                                                        <!-- @ BLOQUE_API_POST_DOC @ -->                  
							<h3>__#Documentación:#__</h3>
							<p>__#Consulta la documentación y los ejemplos de código:#__</p>
							<p>
                                                                <!-- {BTN_DOC} -->
                                                                <!-- {BTN_EJE} -->
							</p>
                                                        <!-- @ BLOQUE_API_POST_DOC @ -->  
							<h3>__#Registro de peticiones:#__</h3>
							<p>__#Consulta el log de las peticiones ejecutadas:#__</p>
							<p>
								<a target="_blank" class="btn btn-primary btn-xs" href="/v4/public/configuracion/avanzada.php?download_post=<!-- {MES_ACTUAL} -->"><!-- {MES_ACTUAL} --></a> 
								<a target="_blank" class="btn btn-primary btn-xs" href="/v4/public/configuracion/avanzada.php?download_post=<!-- {MES_ANTERIOR} -->"><!-- {MES_ANTERIOR} --></a>
							</p>
						</div>
					</div>
					<!--SOAP-->
                                        <!-- @ BLOQUE_DEPRECATED @ --> 
					<div id="p3" class="parrafo" style="display:none;">
						<div class="span4">
							<center>
								<a  id="btnSoap" class="btnApi">
									<i class="icon-retweet icon-3x"></i><!--icon-group-->
									<p>SOAP - API</p>
								</a>
							</center>
						</div>
						<div id="contenidoSoap" class="contenido">
							<h3>__#Descripción:#__</h3>
							<p>__#Antigua SOAP API, actualmente <strong>no es mantenida en favor del POST-API</strong> y futuras apis más sencillas de implementar#__</p>
							<p>__#Permite obtener los datos estadísticos de los envíos realizados con la plataforma#__</p>
							
							<h3>__#Datos de conexión:#__</h3>
							<table class="table table-bordered">
								<tbody>
									<tr>
										<td><strong>Wsdl</strong></td>
										<td><!-- {HOST_POSTSOAPAPI} -->v4/public/api/soap/wsdl.xml</td>
									</tr>
								</tbody>
							</table>
							<h3>__#Documentación:#__</h3>
							<p>__#Consulta la documentación y los ejemplos de código:#__</p>
							<p>
								<a class="btn btn-primary btn-xs" href="https://github.com/teenvio/SOAP-API/" title="teenvio/SMTP-API" target="_blank">__#SOAP-API en GitHub#__</a>
							</p>
						</div>
					</div>
                                        <!-- @ BLOQUE_DEPRECATED @ --> 
				</div>
				
				<div class="total"></div>
				
			</div>
		</div>
	</div>
</div>


<!-- @ BTN_API @ -->
<a class="btn btn-primary btn-xs" href="<!-- {URL} -->" title="<!-- {COMERCIAL_NOMBRE} -->" target="_blank"><!-- {TEXTO} --></a>
<!-- @ BTN_API @ -->