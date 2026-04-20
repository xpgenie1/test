<style type="text/css">
	.colum{float: left; margin:0 20px;width:560px;}
	.colright{width:262px;}
	.colright p{margin:0; padding:0 0 8px 0;}
	.content{margin:30px 0;}
</style>
<script type="text/javascript">
	if (jQuery){jQuery(document).ready(function(){jQuery(".tetooltip, .btn-help").tooltip({trigger:'click'}).mouseout(function(){jQuery(this).tooltip('hide');});});}
</script>

<div class="page">		
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tgeneral" data-toggle="tab"><i class="icon-edit"></i> __#Contacta#__</a></li>
	</ul>
	<div class="tab-content">
		<div class="frm-horizontal">
			
			<div class="row-fluid">
				<div class="colum">
					
					<h3>__#Iniciar consulta#__</h3>
					<hr />
					<div class="content">
						
						<input type="hidden" name="donde_viene" value="<!-- {REFERER} -->" />
						
						<div class="control-group row-fluid">
							<label>__#Nombre#__</label>
							<input type="text" readonly="readonly" placeholder="__#Nombre#__" value="<!-- {NOMBRE} -->" title="__#Nombre#__" name="nombre" class="input-xxlarge obligatorio" />
						</div>
						<div class="control-group row-fluid">
							<label>__#Email#__</label>
							<input type="text" readonly="readonly" placeholder="__#Email#__" value="<!-- {EMAIL} -->" title="__#Email#__" name="email" class="input-xxlarge obligatorio"/>
						</div>
						<div class="control-group row-fluid">
							<label>__#Asunto#__</label>
							<input type="text" placeholder="__#Asunto#__" title="__#Asunto#__" name="asunto" class="input-xxlarge obligatorio" />
						</div>
						<div class="control-group row-fluid">
							<label>__#Comentario#__</label>
							<textarea placeholder="__#Comentario#__" title="__#Comentario#__" name="comentario" class="input-xxlarge" style="min-height: 100px"></textarea>
						</div>
						
						<div class="control-group row-fluid">
							<button class="btn btn-success btn-success-contacta">__#Enviar#__</button> <a class="btn" href="/v4/public/usuarios/listado.php#id=<!-- {ID_USUARIO} -->"><i class="icon-user"></i> __#Actualizar mis datos de usuario#__</a>
						</div>
	
					</div>
				</div>
                                <!-- @ BLOQUE_TEENVIO @ -->                
				<div class="colum colright">
					<h3>__#Contacto directo#__</h3>
					<hr />
					<div class="content">
                                                
                                                <!-- @ BLOQUE_CLIENTES @ -->
						<h3>__#Comercial#__</h3>
						<p><i class="icon-phone"></i> +34 91 523 89 56 - Ext. 1</p>
						<p><i class="icon-edit"></i> <a href="mailto:soporte@teenvio.com" target="_blank">soporte@teenvio.com</a></p>
                                                <h3>__#Soporte#__</h3>
                                                <p><i class="icon-phone"></i> +34 91 523 89 56 - Ext. 2</p>
						<p><i class="icon-edit"></i> <a href="mailto:soporte@teenvio.com" target="_blank">soporte@teenvio.com</a></p>
                                                <!-- @ BLOQUE_CLIENTES @ -->
						
						<!-- @ BLOQUE_GRATUITO @ -->
						<h3>__#Comercial#__</h3>
						<p><i class="icon-phone"></i> +34 91 523 89 56 - Ext. 1</p>
						<p><i class="icon-edit"></i> <a href="mailto:soporte@teenvio.com" target="_blank">soporte@teenvio.com</a></p>
                                                <h3>__#Soporte#__</h3>
						<p><i class="icon-phone"></i> __#No disponible para planes gratuitos#__</p>
                                                <p><i class="icon-edit"></i> <a href="mailto:soporte@teenvio.com" target="_blank">soporte@teenvio.com</a></p>
                                                <!-- @ BLOQUE_GRATUITO @ -->
                                                
						<h3>__#Horario telefónico#__</h3>
						<p><i class="icon-calendar"></i> __#De 9:00 a 18:00 - Julio y Agosto de 8:00 a 15:00 Europe/Madrid (GMT +1)#__</p>
						
						<div class="row-fluid" style="margin-bottom:10px">
							<a class="btn btn-info" target="_blank" href="https://www.teenvio.com/es/soporte/preguntas/"><i class="icon-question-sign"></i> __#Preguntas frecuentes#__</a>
						</div>
						
						<div class="row-fluid">
							<a class="btn btn-info" target="_blank" href="https://www.teenvio.com/es/precios/"><i class="icon-question-sign"></i> __#Precios#__</a>
						</div>
					</div>
				</div>
                                <!-- @ BLOQUE_TEENVIO @ -->    
                                <!-- @ BLOQUE_MBCA @ -->                
				<div class="colum colright">
					<h3>__#Contacto directo#__</h3>
					<hr />
					<div class="content">
                                                <!-- {CONTENIDO} -->
					</div>
				</div>
                                <!-- @ BLOQUE_MBCA @ -->    
                                
			</div>
						
		</div>
	
	</div>
</div>

<!-- @ BLOQUE_DATOS @ -->
        <h3><!-- {TITULO} --></h3>
        <!-- {DATOS} -->
<!-- @ BLOQUE_DATOS @ -->

<!-- @ BLOQUE_TELEFONO @ -->
    <p><i class="icon-phone"></i> <!-- {TELEFONO} --></p>
<!-- @ BLOQUE_TELEFONO @ -->

<!-- @ BLOQUE_EMAIL @ -->
        <p><i class="icon-edit"></i> <a href="mailto:<!-- {EMAIL} -->" target="_blank"><!-- {EMAIL} --></a></p>
<!-- @ BLOQUE_EMAIL @ -->