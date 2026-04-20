
<div class="listado">
	<div id="ficha-cliente">
		<div id="contenido">
		
			<ul class="nav nav-tabs">
			  <li class="active"><a href="#nav-cuenta" data-toggle="tab"><i class="icon-asterisk"></i> __#Mi plan#__</a></li>
			  <li><a href="#nav-datos" data-toggle="tab"><i class="icon-building"></i> __#Datos de cliente#__</a></li>
			  <li><a href="#nav-pedidos" data-toggle="tab"><i class="icon-file"></i> __#Pedidos y facturas#__</a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane fade in active" id="nav-cuenta">
						<div class="row-fluid">
							<div class="span12">
								<div class="botones">
									<a class="btn btnCambioPlan cambioplan" href="#cambio_plan"><i class="icon-asterisk"></i> __#Cambio de plan#__</a> <a class="btn pull-right cancelaplan" href="#cerrar_plan" role="button" data-toggle="modal"><i class="icon-frown"></i> __#Cancelación de plan#__</a></div>
								</div>
						</div>

					<div class="cajadatos">
						<div class="row-fluid">
							<div class="span12">
								<div class="textotitular">Información de plan contratado</div>
							</div>
						</div>
					<div class="row-fluid">
						<div class="span5">
								<div>__#Plan contratado:#__ <strong><!-- {C_PLAN} --></strong></div>
								<div>__#Tipo de plan:#__ <strong><!-- {C_TIPO} --></strong></div>
								
							</div>
							<div class="span7">
								<div>__#Identificador de plan:#__ <strong><!-- {C_NOMBRE} --></strong></div>
								<div>__#Ciclo actual:#__ <strong><!-- {C_DESDE} --> - <!-- {C_HASTA} --></strong></div>
							</div>
						</div>
					</div>
					
					<div id="cambio_plan" class="modal hide fade" role="dialog" >
						
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3><i class="icon-asterisk"></i> __#Cambio de plan#__</h3>
						</div>

						<div class="modal-body">
							<form>
								<div class="paso paso1">
								
									<h3>__#Selecciona el tipo y el plan al que deseas cambiar#__</h3>
									<p>__#Actualmente tienes contratado un#__ <strong><!-- {C_PLAN} --></strong> __#de tipo #__<strong><!-- {C_TIPO} --></strong></p>
									
									
							
							
									<div class="accordion" id="acordeonTipos">
									<!-- @ BLOQUE_TIPO @ -->
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle" data-toggle="collapse" data-parent="#acordeonTipos" href="#collapse_<!-- {TIPO} -->">
													<i class="icon-chevron-down"></i> <!-- {TIPO} -->
												</a>
											</div>
											<div id="collapse_<!-- {TIPO} -->" class="accordion-body collapse">
												<div class="accordion-inner ">
													<!-- {LISTADO} -->
												</div>
											</div>
										</div>
									<!-- @ BLOQUE_TIPO @ -->
									<!-- @ BLOQUE_ITEM_LISTADO_PLANES @ -->
										<label><input type="radio" name="id_plan_selected" value="<!-- {ID_PLAN} -->" <!-- {DISABLED} --> /> <span class="item" id="plan_<!-- {ID_PLAN} -->"><!-- {NOMBRE_PLAN} --> - <!-- {PRECIO_PLAN} --></span> <span class="tetooltip" data-container="body" data-original-title="__#Permite el envío a#__ <!-- {CONTACTOS_ILIMITADOS} --><!-- {CONTACTOS|number_format:0:,:.} --> __#contactos distintos con#__ <!-- {ENVIOS_ILIMITADOS} --><!-- {ENVIOS|number_format:0:,:.} --> __#envíos totales#__"><i class="icon-info-sign"></i></span></label>
										<input type="hidden" name="precio_plan_<!-- {ID_PLAN} -->" value="<!-- {PRECIO_BRUTO} -->"/>
										<input type="hidden" name="envios_plan_<!-- {ID_PLAN} -->" value="<!-- {ENVIOS_BRUTO} -->"/>
										<input type="hidden" name="contactos_plan_<!-- {ID_PLAN} -->" value="<!-- {CONTACTOS_BRUTO} -->"/>
										<input type="hidden" name="tipo_plan_<!-- {ID_PLAN} -->" value="<!-- {TIPO_BRUTO} -->"/>
										<input type="hidden" name="nombre_plan_<!-- {ID_PLAN} -->" value="<!-- {NOMBRE_PLAN} -->"/>
									<!-- @ BLOQUE_ITEM_LISTADO_PLANES @ -->
									</div>
									
								</div>
								<div class="paso paso2 hide">
									
									<style type="text/css">
										#cambio_plan div.paso2 .span6{
											border: 1px solid #CCCCCC;
											border-radius: 5px;
											padding: 8px;
										}

									</style>
									
									<h3>__#¿Cuándo quieres realizar el cambio de plan?#__</h3>
									<p>__#El ciclo actual de tu plan termina el#__ <span class="fecha_fin"><strong><!-- {C_HASTA} --></strong></span></p>
									
									
									<div class="accordion" id="acordeonCiclo">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle" data-toggle="collapse" data-parent="#acordeonCiclo" href="#collapse_ahora">
													<i class="icon-chevron-down"></i>  __#Quiero cambiar ahora#__  <img style="display:block; float:right;" src="/v4/public/img/USU/ciclo-actual-select.png" alt="ciclo-nuevo" width="112" height="20" />
												</a>
											</div>
											<div id="collapse_ahora" class="accordion-body in collapse">
												<div class="accordion-inner">
													<div class="row-fluid">
														<div class="ciclo_nuevo">
															<label>
																<input type="radio" name="modo_cambio" value="nuevo" /> <img src="/v4/public/img/USU/ciclo-nuevo.png" alt="ciclo-nuevo" width="190" height="26" />
															</label>
															__#Quiero iniciar un nuevo ciclo renovando mis contadores de envíos y contactos, pagando la totalidad del plan elegido.#__
														</div>
														<br>
														<div class="ciclo_prorrateado">
															<label>
																<input type="radio" name="modo_cambio" value="prorrateado" /> <img src="/v4/public/img/USU/ciclo-actual.png" alt="ciclo-nuevo" width="165" height="26" />
															</label>
															__#Quiero mantener mi ciclo actual aumentando la capacidad elegida y conservando todo lo consumido hasta ahora, pagando sólo la diferencia con mi plan actual.#__

														</div>
														<br/>
													</div>
												</div>
											</div>
										</div>
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle" data-toggle="collapse" data-parent="#acordeonCiclo" href="#collapse_siguiente">
													<i class="icon-chevron-down"></i> __#Quiero cambiar para el próximo ciclo#__ <img style="display:block; float:right;" src="/v4/public/img/USU/proximo-ciclo-select.png" alt="ciclo-nuevo" width="112" height="20" />
												</a>
											</div>
											<div id="collapse_siguiente" class="accordion-body collapse">
												<div class="accordion-inner">
													<div class="ciclo_siguiente">
														<label>
															<input type="radio" name="modo_cambio" value="siguiente" checked="checked" /> <img src="/v4/public/img/USU/proximo-ciclo.png" alt="ciclo-nuevo" width="165" height="26" />
														</label>
														__#Quiero que el el cambio sea efectivo para el siguiente ciclo y sucesivos#__
													</div>
												</div>
											</div>
										</div>
										
									</div>
								</div>
								
								<div class="paso paso3 hide">
								<h3>__#Procesa tu nuevo pedido#__</h3>
									<p>__#Actualmente tienes contratado un#__ <strong><!-- {C_PLAN} --></strong> __#de tipo #__<strong><!-- {C_TIPO} --></strong></p>
									<p>__#Estás solicitando un cambio a un#__ <strong><span class="nuevo_plan_nombre"></span></strong> __#de tipo #__<strong><span class="nuevo_plan_tipo"></span></strong></p>
									<div class="alert alert-success">
									<div class="descripcion-final">
										<span class="ciclo ciclo_siguiente hide">__#El cambio será efectivo al finalizar el ciclo actual, que termina el #__<!-- {C_HASTA} -->.</span>
										<span class="ciclo ciclo_prorrateado hide">__#El cambio será efectivo desde ahora, dispondrás de la nueva capacidad contratada pero manteniendo el consumo actual de este ciclo. El siguiente ciclo empezará el #__<!-- {C_HASTA} --> __#con los contadores de contactos y envíos ya a cero.#__</span>
										<span class="ciclo ciclo_nuevo hide">__#El cambio será efectivo desde ahora, creando un nuevo ciclo con los contadores de contactos y envíos a cero.#__</span>
									</div>
									</div>
									<p>
										<input id="condiciones" type="checkbox"> __#He leído, comprendo y acepto la#__ <a target="_blank" href="http://www.teenvio.com/es/legal/politica-de-privacidad/">__#Política de Privacidad#__</a>, <a target="_blank" href="http://www.teenvio.com/es/legal/aviso-legal/">__#Aviso Legal#__</a>, <a target="_blank" href="http://www.teenvio.com/es/legal/politica-antispam/">__#Políticas Antispam#__</a> __#y#__ <a target="_blank" href="http://www.teenvio.com/es/legal/condiciones-generales-contratacion/">__#Condiciones de Contratación#__</a>.
									</p>
									<p>__#Al pulsar en “Procesar pedido” <strong>accederás al proceso de compra</strong> para que puedas hacer efectivo tu pedido y el pago si fuese necesario.#__</p>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<div class="row-fluid">
								<div class="span5" style="text-align: left;">
									<a href="/v3/contacto/" class="btn btn-info btnContacto" target="_blank"><i class="icon-question-sign"></i> __#Contacto#__</a> <a href="http://www.teenvio.com/es/precios/" class="btn btn-info" target="_blank" ><i class="icon-question-sign"></i> __#Precios#__</a>
								</div>
								<div class="span7">
									<input type="button" value="__#Atrás#__" class="btn btnAtras hide" />
									<input type="button" value="__#Siguiente#__" class="btn btn-success btnSiguiente" disabled="disabled" />
									<input type="button" value="__#Procesar pedido#__" class="btn btn-success btnFinalizar hide" disabled="disabled" />

									<button class="btn" data-dismiss="modal" aria-hidden="true">__#Cancelar#__</button>
								</div>
							</div>
						</div>

					</div>
					
					
					<div id="cerrar_plan" class="modal hide fade" role="dialog" >
			
						<div class="modal-header" style="background-color: #B94A48;">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3><i class="icon-frown"></i> __#Cancelación de plan#__</h3>
						</div>

						<div class="modal-body">
							<p>__#Te dispones a cerrar tu plan #__ <strong><!-- {C_PLAN} --></strong> __#de tipo #__<strong><!-- {C_TIPO} --></strong></p>
							<div class="alert alert-error"><i class="icon-warning-sign"></i> __#Tu plan quedará inaccesible y será eliminado de nuestros sistemas.#__</div>
							<p>__#Es aconsejable cambiar a un plan gratuito en lugar del cierre.#__</p>
							<p>__#Si finalmente efectuas el cierre, <strong>no se podrá deshacer ni recuperar</strong> ningún dato#__</p>
							
							<p>
								<input id="condiciones" type="checkbox"> __#Deseo cerrar y eliminar mi plan sin posibilidad de recuperación.#__
							</p>
							

						</div>
						<div class="modal-footer">
							<div class="row-fluid">
								<div class="span6" style="text-align: left;">
									<a href="/v3/contacto/" class="btn btn-info btnContacto" target="_blank"><i class="icon-question-sign"></i> __#Contacto#__</a> <a href="http://www.teenvio.com/es/precios/" class="btn btn-info" target="_blank" ><i class="icon-question-sign"></i> __#Precios#__</a>

								</div>
								<div class="span6">									
									<input type="button" value="__#Eliminar plan#__" class="btn btn-danger btnFinalizar" disabled="disabled" />
									<button class="btn" data-dismiss="modal" aria-hidden="true">__#Cancelar#__</button>
								</div>
							</div>
						</div>

					</div>
					
					
				</div>

				<div class="tab-pane fade" id="nav-datos">
					<div class="row-fluid">
						<div class="span12">
							<div class="botones">
									<a class="btn btnCambioPlan cambiofactura" href="#cambio_plan"><i class="icon-building"></i> __#Solicitar cambio de datos Información fiscal#__</a>
							</div>
						</div>
					</div>
					<div class="cajadatos">
					<div class="row-fluid">
							<div class="span12">
								<div class="textotitular">__#Información fiscal#__</div>
							</div>
						</div>
					<form class="fichaCliente form-horizontal" onsubmit="return false;" action="">
				
						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#NIF/CIF#__</label>
							<div class="controls">
								<input type="text" placeholder="__#NIF/CIF#__" name="nif" value="<!-- {D_CIF} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#Razón Social#__</label>
							<div class="controls">
								<input type="text" placeholder="__#Razón Social#__" name="razon_social" value="<!-- {D_RAZON_SOCIAL} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">Dirección línea 1</label>
							<div class="controls">
								<input type="text" placeholder="__#Dirección línea 1#__" name="direccion1" value="<!-- {D_DIRECCION1} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#Dirección línea 2#__</label>
							<div class="controls">
								<input type="text" placeholder="__#Dirección línea 2#__" name="direccion2" value="<!-- {D_DIRECCION2} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#C.P.#__</label>
							<div class="controls">
								<input type="text" placeholder="__#C.P.#__" name="cp" value="<!-- {D_CP} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#Localidad#__</label>
							<div class="controls">
								<input type="text" placeholder="__#Localidad#__" name="localidad" value="<!-- {D_LOCALIDAD} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#Provincia#__</label>
							<div class="controls">
								<input type="text" placeholder="__#Provincia#__" name="provincia" value="<!-- {D_PROVINCIA} -->">
							</div>
						</div>

						<div class="control-group input-append">
							<label for="inputEmail" class="control-label">__#País#__</label>
							<div class="controls">
								<input type="text" placeholder="__#País#__" name="pais" value="<!-- {D_PAIS} -->">
							</div>
						</div>

					</form>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="botones">
									<a class="btn btnCambioPlan cambiofactura" href="#cambio_plan"><i class="icon-building"></i> __#Cambiar datos de contacto#__</a>
							</div>
						</div>
					</div>
					<div class="cajadatos">
						<div class="row-fluid">
							<div class="span6">
								<div class="textosubtitular">__#Contacto general#__</div>
								<form class="fichaCliente form-horizontal" onsubmit="return false;" action="">

									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Teléfono#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Teléfono#__" name="telefono" value="<!-- {D_TELEFONO} -->">
										</div>
									</div>
			
									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Web#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Web#__" name="web" value="<!-- {D_WEB} -->">
										</div>
									</div>
			
									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Email#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Email#__" name="email" value="<!-- {D_EMAIL} -->">
										</div>
									</div>
								</form>
							</div>
							<div class="span6">
								<div class="textosubtitular">__#Contacto facturación#__</div>
								<form class="fichaCliente form-horizontal" onsubmit="return false;" action="">
									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Nombre#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Teléfono#__" name="telefono" value="<!-- {D_TELEFONO} -->">
										</div>
									</div>
									
									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Teléfono#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Teléfono#__" name="telefono" value="<!-- {D_TELEFONO} -->">
										</div>
									</div>
			
									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Web#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Web#__" name="web" value="<!-- {D_WEB} -->">
										</div>
									</div>
			
									<div class="control-group input-append">
										<label for="inputEmail" class="control-label">__#Email#__</label>
										<div class="controls">
											<input type="text" placeholder="__#Email#__" name="email" value="<!-- {D_EMAIL} -->">
										</div>
									</div>
								</form>
							</div>
						</div>
											
											
											
					</div>

					<br/>
					
				</div>

				<div class="tab-pane fade" id="nav-pedidos">
					<h3>Listado de pedidos y facturas</h3>
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
							<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Cerrar</button>
						</div>
					</div>
				</div>
			</div>
			
		
			
		
		</div>
		
	</div>
</div>