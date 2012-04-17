<?php
/**
 * @copyright (c) 2012, Luxbet Pty Ltd. All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Supervisor Servers</title>
		<link href="<?=$url_root?>css/bootstrap.css" media="screen" rel="stylesheet" type="text/css" />
		<link href="<?=$url_root?>css/bootstrap-responsive.css" media="screen" rel="stylesheet" type="text/css" />
		<style type="text/css">
			body {
				padding-top: 60px;
				padding-bottom: 40px;
			}

			.status {
				font-weight: bold;
				font-size: 11pt;
				padding:4px;
				margin-right:10px;
				float:right;
			}

			.fixed-footer {
				position: fixed;
				left: 0;
				right: 0;
				bottom: 0;
			}

			.page-header {
				width: 70%;
			}

			.server-summary, .server-details .page-header {
				cursor: pointer;
			}

			.server-details {
				display: none;
			}

			.services_list {
				padding:10px;
				width:70%;
			}

			.services_list div {
				padding: 10px;
			}
			.services_list div:nth-child(odd) {
			     background: #eee;
			}

			.services_list div:nth-child(even) {
			     background: #ccc;
			}

			.service-count-warning {
				color: red;
			}

			.service-count-ok {
				color: green;
			}

			#servers {
				padding-bottom: 80px;
			}
		  </style>
    </head>
    <body>
		<div class="navbar navbar-fixed-top">
	      <div class="navbar-inner">
	        <div class="container-fluid">
	          <a class="brand" href="#">Supervisor Dashboard</a>
	        </div>
	      </div>
	    </div>

		<div class="container">
			<div id="servers">
				<div id="server-list">
				</div>
			</div>
		</div>

		<div class="modal-footer fixed-footer">
			<footer><a href="https://github.com/luxbet" target="_blank">Luxbet</a></footer>
		</div>

		<script>
			var URL_ROOT = "<?=$url_root?>";
		</script>

		<script src="<?=$url_root?>js/jquery.js"></script>
		<script src="<?=$url_root?>js/bootstrap.js"></script>
		<script src="<?=$url_root?>js/bootstrap-alert.js"></script>
		<script src="<?=$url_root?>js/underscore.js"></script>
		<script src="<?=$url_root?>js/backbone.js"></script>
		<script src="<?=$url_root?>js/supervisor-backbone.js"></script>

		<script type="text/template" id="server-template">
			<div class="server-summary page-header">
				<h3><i class="icon-plus-sign"></i>
					<%= name %>
					<small><%= ip %> (<%= pid %>) <%= statename %> Supervisor version <%= version %> </small><span class="server-summary-details" style="float:right"></span>
				</h3>
			</div>

			<div class="server-details">
				<div class="page-header">
					<h3><i class="icon-minus-sign"></i>
						<%= name %>
						<small><%= ip %> (<%= pid %>) <%= statename %> Supervisor version <%= version %></small>
					</h3>
				</div>
				<div id="<%= id %>_services" class="services_list"></div>
			</div>
		</script>

		<script type="text/template" id="service-template">
				<span style="float:right;">
					<button type="button" class="running_action btn btn-mini <%= running ? 'btn-danger' : 'btn-info' %>" value="<%= running %>"><i class="icon-white <%= running ? 'icon-stop' : 'icon-play' %>"></i></button>
				</span>
				<span class="status label <%= running ? 'label-success' : 'label-important' %>">
					<%= statename %>
				</span>
				<span class="alert alert-error" style="float:right;margin-right:20px;display:none;">
				  <a class="close" data-dismiss="alert">×</a>
				  <h4 class="alert-heading">Error</h4>
				  <span class="alert-message"></span>
				</span>
				<span>
					<h4><% if (group != name) { %> <%= group %> : <% } %> <%= name %></h4>
					<%= description %>
				</span>
		</script>
    </body>
</html>