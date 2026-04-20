<style type="text/css">
hr{ margin-bottom: 20px;margin-top: 10px;}
#cancelacuenta button { margin-left: 15px;}
.form-horizontal .control-group { margin-bottom: 20px;margin-top: 10px;}
.form-horizontal .controls .controls{ margin-left:0;}
.form-horizontal .controls input{ border-radius: 4px;}
.form-horizontal .controls.help input{ border-radius: 4px 0 0 4px;}
#cambio_plan{ width:680px;margin-left:-335px;}
</style>


<div class="listado">
	<div id="ficha-cliente">
		<div id="contenido" class="page">
                    <!-- @ BOTONES_ADMINISTRACION @ -->
                        <div id="cancelacuenta">
				<button class="btn btn-danger cancelaplan pull-right" href="#cerrar_plan" role="button" data-toggle="modal"><i class="icon-frown"></i> __#Cancelar cuenta#__</button>
			</div>
			<button class="btn btnCambioPlan cambioplan pull-right <!-- {BTN_ACTUALIZA_CLASS} -->"><i class="icon-asterisk"></i> __#Actualizar plan#__</button>
                    <!-- @ BOTONES_ADMINISTRACION @ -->
			<ul class="nav nav-tabs">
				<li class="active"><a href="#nav-cuenta" data-toggle="tab"><i class="icon-asterisk"></i> __#Mi plan#__</a></li>
				<!-- @ ENLACES_VISUALIZACION @ -->
                                <li><a href="#nav-datos" data-toggle="tab"><i class="icon-building"></i> __#Datos de cliente#__</a></li>
				<li><a href="#nav-pedidos" data-toggle="tab"><i class="icon-file"></i> __#Pedidos y facturas#__</a></li>
                                <!-- @ ENLACES_VISUALIZACION @ -->
			</ul>

			<div class="tab-content">
				<div class="tab-pane fade in active" id="nav-cuenta">
					<div class="cajadatos cuenta">
								
						<h3>__#Información de plan contratado#__</h3>
						<hr>
								
						<div class="row-fluid">
							<div class="span6">
								<div>__#Plan contratado:#__ <strong><!-- {C_PLAN} --></strong></div>
								<div>__#Tipo de plan:#__ <strong><!-- {C_TIPO} --></strong></div>
								
							</div>
							<div class="span6">
								<div>
									__#Nombre del plan:#__ <strong><!-- {C_NOMBRE} --></strong>
									<a style="float:right;" class="btn" href="/v4/public/configuracion/avanzada.php#section=tapi">__#Datos para APIs#__</a>
								</div>
								<div>__#Ciclo actual:#__ <strong><!-- {C_DESDE} --> - <!-- {C_HASTA} --></strong></div>
							</div>
						</div>
							
                                            <!-- @ BLOQUE_ADMINISTRACION @ -->	
						<h3 style="margin-top:25px;">__#Suscripción#__</h3>
						<hr>
						<!-- {SUSCRIPCION} -->
						
						
						<h3 style="margin-top:25px;">__#Personalización#__</h3>
						<hr>
										
						<div class="row-fluid edit-img">
							<div class="span6">
								<div>__#Imagen cabecera:#__ </div>
								<!-- {EDITAR_IMAGEN} -->
								<form method="post" onsubmit="return false;" name="frmAjax" class="frmAjax form-vertical <!-- {MOSTRAR_SUBIR} -->" target="iframeAjax" enctype="multipart/form-data">
									<input type="file" name="imagen" value="" title="__#Seleccionar Imagen#__" class="obligatorio" />
									<button class="btn btn-success btnModificarImg">__#Modificar Imagen#__</button>
								</form>
								<iframe name="iframeAjax" frameborder="0" style="width:1px;height:1px;"></iframe>
							</div>
							<div class="span6">
								<div>__#Huso Horario:#__</div>
								<!-- {DDL_TIMEZONE} -->
							</div>
						</div>
                                            <!-- @ BLOQUE_ADMINISTRACION @ -->
                                        </div>
				</div>
                            <!-- @ BLOQUE_VISUALIZACION @ -->
				<div class="tab-pane fade form-horizontal" id="nav-datos">
						
					<h3>__#Información fiscal#__</h3>
					<hr>
					<input type="hidden" class="hide" name="id_cliente" value="<!-- {D_ID} -->">
					
					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#NIF/CIF#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#NIF/CIF#__" name="nif" value="<!-- {D_CIF} -->" >
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Razón Social#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Razón Social#__" name="razon_social" value="<!-- {D_RAZON_SOCIAL} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Dirección línea 1#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Dirección línea 1#__" name="direccion1" value="<!-- {D_DIRECCION1} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Dirección línea 2#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Dirección línea 2#__" name="direccion2" value="<!-- {D_DIRECCION2} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#C.P.#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#C.P.#__" class="number" name="cp" value="<!-- {D_CP} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Localidad#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Localidad#__" name="localidad" value="<!-- {D_LOCALIDAD} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="pais" class="control-label">__#País#__</label>
						<div class="controls">
							<!-- {DDL_PAIS} -->
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Provincia#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Provincia#__" name="provincia" value="<!-- {D_PROVINCIA} -->">
							<!-- {DDL_PROVINCIA} -->
						</div>
					</div>
					
					<h3>__#Contacto general#__</h3>
					<hr>


					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Teléfono#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Teléfono#__" class="number" name="telefono" value="<!-- {D_TELEFONO} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Web#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Web#__" name="web" value="<!-- {D_WEB} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Email#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Email#__" name="email" value="<!-- {D_EMAIL} -->">
						</div>
					</div>

					
					<h3>__#Contacto facturación#__</h3>
					<hr>
					
					<input type="hidden" class="hide" name="id_contacto" value="<!-- {F_ID} -->">

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Nombre#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Nombre#__" name="facturacion_nombre" value="<!-- {F_NOMBRE} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Teléfono#__</label>
						<div class="controls">
							<input <!-- {READONLY} --> type="text" placeholder="__#Teléfono#__" class="number"  name="facturacion_telefono" value="<!-- {F_TELEFONO} -->">
						</div>
					</div>

					<div class="control-group input-append">
						<label for="inputEmail" class="control-label">__#Email#__</label>
						<div class="controls help">
							<input <!-- {READONLY} --> type="text" placeholder="__#Email#__" name="facturacion_email" value="<!-- {F_EMAIL} -->">
							<button class="btn btn-help" data-original-title="__#Email utilizado para el envío de facturas exclusivamente#__">
								<i class="icon-question"></i>
							</button>
						</div>
					</div>

					<div class="row-fluid">
						<button class="btn btn-success btnGuardar pull-right <!-- {BOTON_CLASS} -->"><i class="icon-cog"></i> __#Guardar#__</button>
					</div>
				</div>
					

				<div class="tab-pane fade" id="nav-pedidos">
					<!-- {ACCIONES} -->
					<div id="tablapedidos">
						<!-- {TABLA_PEDIDOS} -->
					</div>
					
					<div class="modal hide fade detallepedido">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3>Pedido</h3>
						</div>
						<div class="modal-body">

						</div>
						<div class="modal-footer">
                                                        
							<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">__#Cerrar#__</button>
						</div>
					</div>
				</div>
                            <!-- @ BLOQUE_VISUALIZACION @ -->
                        </div>
			
		
			
		
		</div>
		
	</div>
</div>
<!-- <script>$('input').attr('readonly', true);</script> -->
<!-- {MODAL_ADMINISTRACION} -->

<!-- @ BLOQUE_SUSCRIPCION_NO_DISPONIBLE @ -->
    <p>__#Su plan no permite pagos con suscripción periódica#__</p>
    <p><!-- {DETALLE} --></p>
<!-- @ BLOQUE_SUSCRIPCION_NO_DISPONIBLE @ -->
						
<!-- @ BLOQUE_SUSCRIPCION_DISPONIBLE @ -->
    <p>__#Puede activar la suscripción de pagos mensual para que su plan se renueve automáticamente#__</p>
    <button class="btn btn-success btnActivateSuscription">__#Activar suscripción#__</button>
<!-- @ BLOQUE_SUSCRIPCION_DISPONIBLE @ -->
						
<!-- @ BLOQUE_SUSCRIPCION_ACTIVA @ -->
    <p>__#Su suscripción está activa.<br> El siguiente cobro de %% € se realizará el %%.||<!-- {IMPORTE} -->|<!-- {FECHA} -->#__</p>
    <button class="btn btn-danger btnCancelSuscription">__#Cancelar suscripción#__</button>
<!-- @ BLOQUE_SUSCRIPCION_ACTIVA @ -->

<!-- @ EDITAR_IMAGEN @ -->
    <img src="<!-- {SRC_IMAGE} -->" style="max-height:80px;max-width:100%;"/><br/>
    <button class="btn btnEliminarImg">__#Eliminar Imagen#__</button>
<!-- @ EDITAR_IMAGEN @ -->