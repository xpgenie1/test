<style type="text/css">
	.tetooltip { color:#cccccc;cursor:pointer;font-size:22px;margin-left:5px;margin-right:5px;position:relative;width:auto;}
	.form-horizontal .control-label {min-width: 170px;margin-left: 20px;text-align:left;padding-top: 5px;}
	.form-horizontal h3{float:left; margin-top:0px}
	.files-menu{height:20px; margin:0 20px 20px 20px;}
	.files-menu ul, .files-path ul{list-style:none;display: inline-block;margin:0;float:right;}
	.files-path ul{float:left}
	.files-menu ul icon{font-size: 18px; color: #58b174;}
	.files-menu ul a icon{color: #58b174;font-size: 18px;}
	.files-path li {float:left; min-width:30px;}
	.files-menu li {float:left; min-width:30px;padding-right: 20px;}
	.files-path li a{margin: 2px; color: #387599}
	.files-path{font-style: italic; height: 40px;font-size: 18px}
	.files-list .folder span{margin-left:20px;}
	.files-list .icon-link, .files-list .icon-cloud-download, .files-list .icon-trash{color: #58b174;padding: 0 10px 0 0;}
	.files-list .control-group .name-file{width:300px;cursor:pointer; float: left;display:block;}
	.files-list .control-group span{font-size:13px;color:#999999;display:block; min-width:70px; margin-right:12px;float:left;}
	.files-list .control-group.folder span{padding-top: 10px;margin-left:2px;}
	.files-list .control-group span i{font-size:14px;padding-left: 5px;}
	.files-list .control-group .fecha-add{float:right;padding:10px 28px;font-size:14px}
	.files-list .control-group .fecha-add i{color:#000000; padding-right:14px;}
	.files-list a{cursor:pointer;}
	.control-group .files{position:relative}
	.control-group .files{margin-top: 5px}
	.get-link{display:none}
	.files-list .control-group .get-link-active {background: none repeat scroll 0 0 #ffffff; border: 2px solid #ccc; border-radius: 2px; box-shadow: 0 0 1px 1px #E0E0E0; left: 108px; padding: 10px; position: absolute; top: -10px; z-index: 100;font-size:14px; color:#387599;}
	.files-menu input[type=file]{display:none;}
	.files-menu form{float:left}
	#modal-preview{margin-left: -335px;  width: 750px;}
	.form-horizontal .clean{border:0;width:260px;cursor: pointer; box-shadow:none;padding-bottom: 6px}
	.form-horizontal .clean:active {box-shadow:none}
	.form-horizontal .clean:focus{width:260px}
	.folder .icon-pencil{cursor:pointer;float:left;margin-top:11px;display:none}
	.folder .edit-folder{width:20px;height:20px;float:left}
</style>
<div class="page">		
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tgeneral" data-toggle="tab"><i class="icon-file"></i> __#Ficheros#__</a></li>
		</ul>
	<div class="tab-content">
		<div class="tab-pane fade in active" id="tgeneral">
			<input type="hidden" name="plan" value="<!-- {PLAN} -->" />
			<div class="form-horizontal">
				<div class="files-menu">	
					<ul>
						<li>
						 	<a href="#" title="__#Subir fichero a teenvio#__">
						 	<form enctype="multipart/form-data" method="post" target="iframeAjax" name="frmupload">
						 		<input type="file" name="fichero" />
						 		<input type="hidden" name="path" />
						 		
						 	</form> <i class="icon-upload"> </i> <i class="icon-file"></i>
						 	</a>
						</li>
						
						<li>
							<a href="#" title="__#Crear carpeta#__"><i class="icon-plus-sign"> </i> <i class="icon-folder-close-alt"></i></a>
						</li>
					</ul>
				</div>
				
				
				<div class="files-path">	
					<ul>
						<li>
						 	<a href="#/">
						 		<i class="icon-folder-open-alt"></i> __#Inicio#__ 
						 	</a>
						</li>
					</ul>
				</div>
					<hr>
			
				<div class="files-list">
                                        
					<!-- @ BLOQUE_CARPETAS @ -->
					
						<div class="control-group folder">
							<div class="control-label name-file" data-name="<!-- {NOMBRE_CARPETA} -->">
								<i class="icon-folder-close-alt"></i> <input type="text" value="<!-- {NOMBRE_CARPETA} -->" class="clean">
							</div>
							<div class="edit-folder"><i class="icon-pencil" title="__#Editar el nombre de la carpeta#__"> </i></div>
							<span>__#Carpeta#__  <a href="#<!-- {ENLACE_FICHERO} -->" title="__#Eliminar carpeta y todo su contenido#__"> <i class="icon-trash"></i></a> </span>
							<span class="fecha-add" title="__#Fecha de creación#__"><!-- {FECHA_CARPETA} --></span>
						</div>
						<hr>
					<!-- @ BLOQUE_CARPETAS @ -->
	
					<!-- @ BLOQUE_FICHEROS @ -->
						<div class="control-group">
							<label class="control-label name-file">
								<i class="<!-- {ICON_FILE} -->"> </i>
								<input type="text" title="<!-- {NOMBRE_FICHERO} -->" value="<!-- {NOMBRE_FICHERO} -->" name="name-file" class="clean" />
								
							</label>
							<div class="control-label files">
								<span title="__#Tamaño#__"><!-- {SIZE_FICHERO} --></span>
                                                                
								<a data-name="<!-- {ENLACE_FICHERO} -->" title="__#Enlace del fichero#__"><i class="icon-link"> </i></a>
								<span class="get-link"></span>
								<a data-name="<!-- {ENLACE_FICHERO} -->" title="__#Descargar fichero#__"><i class="icon-cloud-download"> </i></a>
								<a data-name="<!-- {ENLACE_FICHERO} -->" title="__#Eliminar fichero#__"><i class="icon-trash"></i></a>
                                                                <button data-name="<!-- {ENLACE_FICHERO} -->" class="btn btn-success hidden">__#Insertar#__</button>
							</div>
							
							<span class="fecha-add" title="__#Fecha de subida#__"><a class="<!-- {OCULTA_PREVIEW} -->" data-name="<!-- {ENLACE_FICHERO} -->" title="__#Previsualizar fichero#__"><i class="icon-laptop"> </i></a> <!-- {FECHA_FICHERO} --></span>
						</div>
										
						<hr>
					<!-- @ BLOQUE_FICHEROS @ -->
				
				</div>
			</div>
			<iframe frameborder="0" style="width:1px;height:1px;" name="iframeAjax"></iframe>	
	
		</div>
	</div>
</div>

<!-- MODAL CREAR CARPETA -->
<div id="modal-nueva-carpeta" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><i class="icon-folder-close-alt"></i> __#Nueva Carpeta#__</h3>
	</div>
	<div class="modal-body">
			<div class="control-group">
				<label class="control-label" for="inputError">__#Nombre de la carpeta#__</label>
				<div class="controls row-fluid">
					<input type="text" name="name_folder" value="" title="__#Nombre carpeta#__" class="obligatorio input-xlarge" />
					<span class="help-inline">__#Campo obligatorio#__</span>
				</div>
			</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success" id="save_folder">__#Guardar#__</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">__#Cerrar#__</button>
	</div>
</div>
<!-- MODAL CREAR CARPETA -->

<!-- MODAL PREVIEW -->
<div id="modal-preview" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><i class="icon-laptop"></i> __#Previsualización#__</h3>
	</div>
	<div class="modal-body">
			<div class="control-group">
				<div class="controls row-fluid">
					
				<iframe src="" id="iframePreview" width="700" height="380" frameborder="0"></iframe>
				</div>
			</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">__#Cerrar#__</button>
	</div>
</div>
<!-- MODAL PREVIEW -->

<script type="text/javascript">
	if (jQuery){jQuery(document).ready(function(){jQuery(".tetooltip, .btn-help").tooltip({trigger:'click'}).mouseout(function(){jQuery(this).tooltip('hide');});});}
</script>