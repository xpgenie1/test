<style type="text/css">
	#fichaContacto{ width:570px;}
	#fichaContacto .nav-tabs > li > a, .nav-pills > li > a{ margin-right: 1px;}
	#fichaContacto button.btn.vincular{ width:221px;}
	#fichaContacto span.clear.add-on{ cursor:pointer;}
	#fichaContacto .external_auth{ padding-top: 10px;}
	#fichaContacto .tab-content{ margin:0;}
	#dgrupos .h3usuarios label{ float:left;margin:0;display:inline-block; width:444px; height: 20px; border:1px solid #cccccc; border-radius: 3px 0 0 3px;border-right: none;padding:4px 12px;}
	#dgrupos .h3usuarios button.btn-help{ float:left;border-radius:0 3px 3px 0;width:30px;}
	#dgrupos div.row-fluid{ margin-bottom:10px;}
	#dgrupos input[type=checkbox]{ visibility: hidden; width:1px; position:absolute;top:1px;left:1px;}
	#dgrupos i.on{ display: none;}
	#dgrupos .controls{ padding:0;margin:0 0 0 5px;display:inline-block;}
	#dgrupos h3{ margin:10px 0 20px 0;}
	
	#dseguridad label.control-label{ width:300px;}
	
	#dacceso input[name=rso_localtoken]{ cursor:text;}
</style>
       
	<div id="fichaContacto" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><i class="icon-contacto"></i> <span>__#Usuario#__</span></h3>
		</div>
		<div class="modal-body">
			
			<ul class="nav nav-tabs">
				<li class="active"><a href="#dacceso" data-toggle="tab">__#Acceso#__</a></li>
				<li><a href="#dpersonales" data-toggle="tab">__#Datos#__</a></li>
				<li><a href="#dmetadatos" data-toggle="tab">__#Metadatos#__</a></li>
				<li><a href="#dseguridad" data-toggle="tab">__#Seguridad#__</a></li>
				<!-- @ BLOQUE_IS_ADMIN_A @ -->
				<li><a href="#dgrupos" data-toggle="tab">__#Grupos / Permisos#__</a></li>
				<!-- @ BLOQUE_IS_ADMIN_A @ -->
			</ul>
			<div class="tab-content form-horizontal">
				
				<div class="tab-pane fade in active" id="dacceso">
					
					<h3>__#Datos de acceso:#__</h3>
					
					<!-- {CAMPOS_ACCESO} -->
					
				<!-- @ BLOQUE_PLAN @ -->
					<span class="name-plan"> .<!-- {PLAN} --> </span>
				<!-- @ BLOQUE_PLAN @ -->
					
				<!-- @ BLOQUE_PASSWORD @ -->	
					<div class="control-group input-append">
						<label class="control-label" for="inputError">__#Contraseña#__</label>
						<div class="controls">
							<input type="password" <!-- {READONLY} --> name="password" value="" title="__#Contraseña#__" />
							<span class="help-inline">__#Campo obligatorio#__</span>
						</div>
					</div>		
					<div class="control-group input-append">
						<label class="control-label" for="inputError">__#Confirmar contraseña#__</label>
						<div class="controls">
							<input type="password" <!-- {READONLY} --> name="confirmar_password" value="" title="__#Confirmar Contraseña#__" />
							<span class="help-inline">__#Campo obligatorio#__</span>
						</div>
					</div>			
				<!-- @ BLOQUE_PASSWORD @ -->
				<!-- @ BLOQUE_EXTERNAL_OAUTH @ -->	
					<div class="external_auth hide">
						<h3>__#Accesos autorizados con:#__</h3>
                                                <!-- {BOTONES_OAUTH} -->
						
						<div class="control-group input-append">
							<label class="control-label" for="inputError">__#Token API#__</label>
							<div class="controls">
								<input type="text" class="hide rsoprofile" readonly="readonly" name="rso_localtoken" value="" />
								<button class="btn vincular" id="rsoAuthlocaltoken" data-provider="localtoken">__#Generar#__</button>
								<span title="__#Revocar autorización#__" class="clear add-on" id="revocar_localtoken"><i class="icon-remove"></i></span>
								<span class="add-on"><i class="icon-puzzle-piece"></i></span>
							</div>
						</div>
						
					</div>
				<!-- @ BLOQUE_EXTERNAL_OAUTH @ -->	
				</div>
				
				<div class="tab-pane fade" id="dpersonales">
					<h3>__#Personales#__:</h3>
					
					<!-- {CAMPOS_PERSONALES} -->
				</div>
				<div class="tab-pane fade" id="dseguridad">
					
					<h3>__#Seguridad#__:</h3>
					
					<div class="control-group input-append">
						<label class="control-label" for="inputError">__#Mandar email en cada acceso a la herramienta#__</label>
						<div class="controls" style="margin-left:310px">
							<div class="ui-flipswitch ui-shadow-inset ui-bar-inherit ui-corner-all ui-flipswitch-active ">
								<a href="#" class="ui-flipswitch-on ui-btn ui-shadow ui-btn-inherit">__#Si#__</a>
								<span class="ui-flipswitch-off">__#No#__</span>
								<input type="checkbox" checked="" id="email_aviso_login" name="email_aviso_login" data-role="flipswitch" class="ui-flipswitch-input" tabindex="-1">
							</div>
						</div>
					</div>
					
					<div class="control-group input-append">
						<label class="control-label" for="inputError">__#Caducar contraseña cada 30 días#__</label>
						<div class="controls" style="margin-left:310px">
							<div class="ui-flipswitch  ui-shadow-inset ui-bar-inherit ui-corner-all ui-flipswitch-active ">
								<a href="#" class="ui-flipswitch-on ui-btn ui-shadow ui-btn-inherit">__#Si#__</a>
								<span class="ui-flipswitch-off">__#No#__</span>
								<input type="checkbox" checked="" id="caduca_password" name="caduca_password" data-role="flipswitch" class="ui-flipswitch-input" tabindex="-1">
							</div>
						</div>
					</div>
					
					<div class="control-group input-append <!-- {SECURE_NET} -->">
						<label class="control-label" for="inputError">__#Red permitida#__<br><span class="option-disabled">(<a target="_blank" href="/v4/public/contacta/" style="cursor:help;"> <i class="icon-question-sign"></i> __#Deshabilitado para tu plan#__</a>)</span></label>							
						<div class="controls" style="margin-left:310px">
							<input type="text" <!-- {SECURE_NET} --> name="red_permitida" id="red_permitida" placeholder="0.0.0.0/8" class="input-medium">
							<button data-original-title="__#Si se indica, sólo se permite el acceso desde la red o redes especificadas.#__" class="btn btn-help"><i class="icon-question"></i></button>
						</div>
					</div>
					
					<h3>__#Doble factor de autenticación#__:</h3>
                                        <!-- @ BLOQUE_DOUBLE_OPT_EMAIL_ENABLED @ -->
                                        <div class="control-group input-append">
						<label class="control-label" for="inputError">__#Por email#__</label>							
						<div class="controls" style="margin-left:310px">
							<div class="ui-flipswitch  ui-shadow-inset ui-bar-inherit ui-corner-all">
								<a href="#" class="ui-flipswitch-on ui-btn ui-shadow ui-btn-inherit">__#Si#__</a>
								<span class="ui-flipswitch-off">__#No#__</span>
								<input type="checkbox" id="doble_factor_email" name="doble_factor_email" data-role="flipswitch" class="ui-flipswitch-input" tabindex="-1">
							</div>
						</div>
                                        </div>
                                        <!-- @ BLOQUE_DOUBLE_OPT_EMAIL_ENABLED @ -->
                                        <!-- @ BLOQUE_DOUBLE_OPT_EMAIL_DISABLED @ -->
                                        <div class="control-group input-append disabled">
						<label class="control-label" for="inputError">__#Por email#__ <span class="option-disabled">(<a target="_blank" href="/v4/public/contacta/" style="cursor:help;"> <i class="icon-question-sign"></i> __#Deshabilitado para tu plan#__</a>)</span></label>							
						<div class="controls" style="margin-left:310px">
                                                        <img class="disable-switch" src="/v4/public/img/ENV/disable-switch/<!-- {LANG} -->.png">
                                                        <input type="hidden" id="doble_factor_email" name="doble_factor_email" value="0">
						</div>
					</div>
                                        <!-- @ BLOQUE_DOUBLE_OPT_EMAIL_DISABLED @ -->
                                        <!-- @ BLOQUE_DOUBLE_OPT_SMS_ENABLED @ -->
					<div class="control-group input-append">
						<label class="control-label" for="inputError">__#Por SMS#__</label>							
						<div class="controls" style="margin-left:310px">
							<div class="ui-flipswitch  ui-shadow-inset ui-bar-inherit ui-corner-all">
								<a href="#" class="ui-flipswitch-on ui-btn ui-shadow ui-btn-inherit">__#Si#__</a>
								<span class="ui-flipswitch-off">__#No#__</span>
								<input type="checkbox" id="doble_factor_sms" name="doble_factor_sms" data-role="flipswitch" class="ui-flipswitch-input" tabindex="-1">
							</div>
						</div>
					</div>
					<!-- @ BLOQUE_DOUBLE_OPT_SMS_ENABLED @ -->
                                        <!-- @ BLOQUE_DOUBLE_OPT_SMS_DISABLED @ -->
					<div class="control-group input-append disabled">
						<label class="control-label" for="inputError">__#Por SMS#__ <span class="option-disabled">(<a target="_blank" href="/v4/public/contacta/" style="cursor:help;"> <i class="icon-question-sign"></i> __#Deshabilitado para tu plan#__</a>)</span></label>							
						<div class="controls" style="margin-left:310px">
							<img class="disable-switch" src="/v4/public/img/ENV/disable-switch/<!-- {LANG} -->.png">
                                                        <input type="hidden" id="doble_factor_sms" name="doble_factor_sms" value="0">
						</div>
					</div>
					<!-- @ BLOQUE_DOUBLE_OPT_SMS_DISABLED @ -->
					<div class="control-group input-append <!-- {DOUBLE_OPT_SECURE_NET} -->">
                                            <label class="control-label" for="inputError">__#Red segura sin doble factor de autenticación#__ <br><span class="option-disabled">(<a target="_blank" href="/v4/public/contacta/" style="cursor:help;"> <i class="icon-question-sign"></i> __#Deshabilitado para tu plan#__</a>)</span></label>							
						<div class="controls" style="margin-left:310px">
							<input type="text" <!-- {DOUBLE_OPT_SECURE_NET} --> name="red_segura" id="red_segura" placeholder="127.0.0.0/24" class="input-medium">
							<button data-original-title="__#Red o redes desde la que se permite el acceso mediante WEB sin solicitar el doble factor de autenticación#__" class="btn btn-help"><i class="icon-question"></i></button>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="dmetadatos">	
					<h3>__#Metadatos#__:</h3>
					<!-- {CAMPOS_METADATOS} -->
				</div>
				<div class="tab-pane fade" id="dgrupos">
					
					<div class="alert alert-block alert-warning"><strong>__#¡Configuración avanzada!#__</strong><br>__#No se recomienda modificar esta configuración a menos que se conozca su funcionamiento#__</div>
					
					<h3>__#Perfiles <span style="font-size:12px;"> - Grupos del sistema</span>#__</h3>
					
					<div class="h3usuarios">
					
						<!-- @ BLOQUE_GRUPO @ -->
						<div class="row-fluid g_<!-- {GRUPO_ID} -->">
							<label><i class="on icon-check"></i><i class="off icon-check-empty"></i><input type="checkbox" name="group_<!-- {GRUPO_ID} -->" value="1" /> <!-- {GRUPO_ID} --> - <!-- {GRUPO_NOMBRE} --></label>
							<button data-original-title="<!-- {GRUPO_DESCRIPCION} -->" class="btn btn-help"><i class="icon-question"></i></button>
						</div>
						<!-- @ BLOQUE_GRUPO @ -->
						<!-- {GRUPOS_SISTEMA} -->

						<!-- @ BLOQUE_GRUPOS_PERSONALIZADOS @ -->
						<br>
						<h3>__#Grupos <span style="font-size:12px;"> - Grupos personalizados</span>#__</h3>
						<!-- {GRUPOS_PERSONALIZADOS} -->					
						<!-- @ BLOQUE_GRUPOS_PERSONALIZADOS @ -->
					
					</div>
					
					<br>
					
					<h3>__#Permisos <span style="font-size:12px;"> - Permisos por defecto para nuevos elementos</span>#__</h3>
					
					<div class="h3masks">
					
						<div class="accordion" id="accordion2">
							
							<!-- @ BLOQUE_ACORDEON_PERMISOS_DEFECTO @ -->
							
							<div class="accordion-group">
								<div class="accordion-heading">
									<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse<!-- {PREFIJO} -->">
										<!-- {NOMBRE} -->
									</a>
								</div>
								<div id="collapse<!-- {PREFIJO} -->" class="accordion-body collapse">
									<div class="accordion-inner">
										<p>__#Permisos#__</p>
										<div class="control-group input-append">
											<label class="control-label" for="perdefault_<!-- {PREFIJO} -->_propietario">__#Propietario#__</label>
											<div class="controls">
												<select name="perdefault_<!-- {PREFIJO} -->_propietario">
													<option value="4">__#Lectura posible#__</option>
													<option value="6" selected>__#Lectura y modificación posibles#__</option>
													<option value="0">__#Prohibido#__</option>
												</select>
												<button data-original-title="__#Permisos para el propietario del elemento#__" class="btn btn-help"><i class="icon-question"></i></button>
											</div>
										</div>

										<div class="control-group input-append">
											<label class="control-label" for="perdefault_<!-- {PREFIJO} -->_grupo">__#Grupo#__</label>
											<div class="controls">
												<select name="perdefault_<!-- {PREFIJO} -->_grupo">
													<option value="4">__#Lectura posible#__</option>
													<option value="6" selected>__#Lectura  y modificación posibles#__</option>
													<option value="0">__#Prohibido#__</option>
												</select>
												<button data-original-title="__#Permisos para los miembros del grupo del elemento#__" class="btn btn-help"><i class="icon-question"></i></button>
											</div>
										</div>
										<div class="control-group input-append">
											<label class="control-label" for="perdefault_<!-- {PREFIJO} -->_resto">__#Resto#__</label>
											<div class="controls">
												<select name="perdefault_<!-- {PREFIJO} -->_resto">
													<option value="4">__#Lectura posibles#__</option>
													<option value="6">__#Lectura  y modificación posibles#__</option>
													<option value="0" selected>__#Prohibido#__</option>
												</select>
												<button data-original-title="__#Permisos para el resto de usuarios#__" class="btn btn-help"><i class="icon-question"></i></button>
											</div>
										</div>
										<p>__#Grupo asignado#__</p>
										<div class="control-group input-append">
											<label class="control-label" for="perdefault_<!-- {PREFIJO} -->_grupoitem">__#Grupo#__</label>
											<div class="controls">
												<!-- {DDL_GRUPOS_USUARIOS} -->
												<button data-original-title="__#Grupo al que petenecera el elemento cuando el usuario lo cree#__" class="btn btn-help"><i class="icon-question"></i></button>
											</div>
										</div>
									</div>
								</div>
							</div>
									
							<!-- @ BLOQUE_ACORDEON_PERMISOS_DEFECTO @ -->
							
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="dpermisos">
					<!-- {CAMPOS_PERMISOS} -->
				</div>		
				<!-- @ BLOQUE_INPUT_CONTACTOS @ -->
					<div class="control-group input-append">
						<label class="control-label" for="inputError"><!-- {LABEL} --></label>
						<div class="controls">
							<input type="text" <!-- {MAXLENGTH} --> <!-- {READONLY} --> name="<!-- {NOMBRE} -->" value="" title="<!-- {NOMBRE} -->" />
							<span class="help-inline">__#Campo obligatorio#__</span>
						</div>
					</div>
				<!-- @ BLOQUE_INPUT_CONTACTOS @ -->

				<!-- @ BLOQUE_TEXT_CONTACTOS @ -->
					<div class="control-group input-append">
						<label class="control-label" for="inputError"><!-- {LABEL} --></label>
						<div class="controls">
							<textarea name="<!-- {NOMBRE} -->" title="<!-- {NOMBRE} -->"></textarea>
						</div>
					</div>
				<!-- @ BLOQUE_TEXT_CONTACTOS @ -->
				
				<!-- @ BLOQUE_SELECT_PAIS @ -->
					<div class="control-group input-append">
						<label class="control-label" for="inputError"><!-- {LABEL} --></label>
						<div class="controls">
							<!-- {DDL_PAIS} -->
						</div>
					</div>
				<!-- @ BLOQUE_SELECT_PAIS @ -->
                                
			</div>
			
		</div>
		<div class="modal-footer">
			<button class="btn btn-success" id="btnGuardaContacto"><i class="icon-cog"></i> __#Guardar#__</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">__#Cerrar#__</button>
		</div>
	</div>
						
	<div id="fichaGrupo" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><i class="icon-contacto"></i> <span>__#Grupo de usuarios#__</span></h3>
		</div>
		<div class="modal-body form-horizontal">
			<div class="control-group input-append">
				<label class="control-label" for="grupo_id">__#Id#__</label>
				<div class="controls">
					<input type="text" name="grupo_id" value="" title="__#Id#__" readonly="readonly" />
				</div>
			</div>
			<div class="control-group input-append">
				<label class="control-label" for="grupo_nombre">__#Nombre#__</label>
				<div class="controls">
					<input type="text" name="grupo_nombre" value="" title="__#Nombre#__" />
					<span class="help-inline">__#Campo obligatorio#__</span>
				</div>
			</div>
			<div class="control-group input-append">
				<label class="control-label" for="grupo_descripcion">__#Descripción#__</label>
				<div class="controls">
					<textarea name="grupo_descripcion" title="__#Descripción#__"></textarea>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-success" id="btnGuardaGrupo"><i class="icon-cog"></i> __#Guardar#__</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">__#Cerrar#__</button>
		</div>
	</div>

	<div id="listado-usuarios" class="page">

		<!-- @ BLOQUE_IS_ADMIN_B @ -->
		<button class="btn pull-right nuevo-usuario"><i class="icon-user"></i> __#Nuevo usuario#__</button>
		<button class="btn pull-right nuevo-grupo-usuarios" style="margin-right:5px"><i class="icon-group"></i> __#Nuevo grupo de usuarios#__</button>
		<!-- @ BLOQUE_IS_ADMIN_B @ -->
				
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tusuarios" data-toggle="tab"><i class="icon-user"></i> __#Usuarios#__</a></li>
			<!-- @ BLOQUE_IS_NOT_ADMIN @ -->
			<li><a href="#tadmins" data-toggle="tab"><i class="icon-wrench"></i> __#Administradores#__</a></li>
			<!-- @ BLOQUE_IS_NOT_ADMIN @ -->
			<!-- @ BLOQUE_IS_ADMIN_C @ -->
			<li><a href="#tusuariosgrupos" data-toggle="tab"><i class="icon-group"></i> __#Grupos de usuarios#__</a></li>
			<!-- @ BLOQUE_IS_ADMIN_C @ -->
		</ul>
		
		<div class="tab-content">
			<div class="tab-pane fade in active" id="tusuarios">			
				<div id='tablaUsuarios' class="<!-- {CLASE_TABLE} -->">
					<!-- {TABLA} -->
				</div>
			</div>
			
			<!-- @ BLOQUE_ADMINS @ -->
			<div class="tab-pane fade" id="tadmins">
				<table class="table table-bordered table-striped table-hover">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>__#Nombre#__</th>
						<th>__#Apellidos#__</th>
						<th>__#Usuario#__</th>
						<th>__#Email#__</th>
					</tr>
				</thead>
				<tbody>
					<!-- {ADMINS} -->
				</tbody>
				</table>
			</div>
			<!-- @ BLOQUE_ADMINS @ -->
			
			<div class="tab-pane fade" id="tusuariosgrupos">
				<div id='tablaUsuariosGrupos' class="">
					<!-- {TABLA_GRUPOS} -->
				</div>
			</div>
		</div>

<!-- @ BLOQUE_OAUTH_PROVIDER @ -->
<div class="control-group input-append">
        <label class="control-label" for="inputError"><!-- {NOMBRE} --></label>
	<div class="controls">
		<input type="text" class="hide rsoprofile" readonly="readonly" name="rso_<!-- {NOMBRE} -->" value="" />
		<button class="btn vincular" id="rsoAuth<!-- {NOMBRE} -->" data-provider="<!-- {NOMBRE} -->">__#Vincular#__</button>
		<span title="__#Revocar autorización#__" class="clear add-on" id="revocar_<!-- {NOMBRE} -->"><i class="icon-remove"></i></span>
		<span class="add-on"><i class="icon-<!-- {CLASS} -->"></i></span>
	</div>
</div>
<!-- @ BLOQUE_OAUTH_PROVIDER @ -->