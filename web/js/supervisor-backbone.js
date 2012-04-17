 /**
  * @copyright (c) 2012, Luxbet Pty Ltd. All rights reserved.
  * @license http://www.opensource.org/licenses/BSD-3-Clause
  */
 $(function() {
	var RUNNING_STATE = 20;

	// server model
	var SupervisorServer = Backbone.Model.extend({
		url: function() {
			return 'http://'+this.get("ip") + URL_ROOT + 'server/details/' + this.get("id");
		},

		el: $("#servers"),

		defaults: function() {
			return {
				id: 0,
				name: "",
				ip: "",
				group: "",
				statecode: 0,
				statename: '',
				version: '',
				services: {},
				pid: 0,
				totalServices: 0,
				runningServices: 0
			};
		},

		initialize: function() {
			this.services = new	SupervisorServiceList;
			this.services.server = this;
			this.services.bind('add', this.addOne, this);
			this.services.bind('reset', this.addAll, this);
			this.services.bind('all', this.render, this);

			this.cid = this.get("name");
		},

		addOne: function(service) {
			var view = new SupervisorServiceView({model: service});
			$("#" + service.collection.server.get("id") + "_services").append(view.render().el);
		},

		addAll: function() {console.log('Adding all services');
			this.set("totalServices", 0);
			this.set("runningServices", 0);
			this.services.each(this.addOne);
			this.countServices();
    	},

		countServices: function() {
			this.set("totalServices", 0);
			this.set("runningServices", 0);
			this.services.each(function(service) {
				service.collection.server.set("totalServices", service.collection.server.get("totalServices") + 1);
				if (service.get("state") == RUNNING_STATE) {
					service.collection.server.set("runningServices", service.collection.server.get("runningServices") + 1);
				}
			});
			console.log("total: "+this.get("totalServices")+" running: "+ this.get("runningServices"));
		},

        toggleSummary: function() {
            alert('toggled!');
        }
	});

	var SupervisorServerList = Backbone.Collection.extend({
		model: SupervisorServer,
		url: function() {
			return URL_ROOT + 'server/list.json'
		}
	});

	var SupervisorServers = new SupervisorServerList;

	var SupervisorServerView = Backbone.View.extend({

        summaryShown: true,

		tagName: "div",

		template: _.template($('#server-template').html()),

        events: {
     		"click .server-summary" : "toggleSummary",
            "click .server-details" : "toggleSummary"
      	},

		initialize: function(options) {
			this.render = _.bind(this.render, this);
			this.model.bind("change:version", this.render);
			this.model.bind("change:totalServices", this.updateServiceCounts, this);
			this.model.bind("change:runningServices", this.updateServiceCounts, this);
		},

        toggleSummary: function() {
            this.$el.find('.server-summary').toggle();
            this.$el.find('.server-details').toggle();
        },

		render: function() {
			// hack: only fetch the services once we have fetched the server details
			if (this.model.get("version")) {
				this.model.services.fetch();
			}

			this.$el.html(this.template(this.model.toJSON()));
			return this;
		},

		updateServiceCounts: function() {
			var total = parseInt(this.model.get("totalServices"));
			var running = parseInt(this.model.get("runningServices"));

			var class_name = (total != running) ? "service-count-warning" : "service-count-ok";

			var html = '<span class="' + class_name + '">'+ running + " of " + total + " running</span>";

 			this.$el.find('.server-summary-details').html(html);
		}
	});

	var SupervisorService = Backbone.Model.extend({

		syncSuccess: false,
		syncError: "",

		url: function() {
			return 'http://'+this.collection.server.get("ip") + URL_ROOT + 'service/' +  this.collection.server.get("id") + "/"
				+ ((this.get('group') !=  this.get("name")) ? this.get('group') + ":" : "") + this.get("name");
		},

		initialize: function() {
			this.cid = this.collection.server.get("name") + ":" + this.get("name");
			this.set({running: (this.get("state") == 20) });
			// We want to refresh after an action
			this.bind("sync", this.onUpdateSuccess, this);
			this.bind("change", this.collection.server.countServices, this.collection.server);
		},

		defaults: function() {
			return {
				name: '',
				description: '',
				state: 0,
				status_name: '',
				running: false
			};
		},

		toggleRunning: function() {
			this.save({running: !this.get("running")});
		},

		onUpdateSuccess: function(service, response) {
			if (response === true) {
				service.syncSuccess = true;
			} else {
				service.syncSuccess = false;
				service.syncError = (typeof response.error != 'undefined')
					? response.error.msg
					: "Error communicating with the server";
			}

			service.fetch();
		},

		sync: function(method, model, options) {
			var params = _.clone(options);
			params.contentType = 'application/json';
			params.data = JSON.stringify({running: model.get('running')});
			Backbone.sync(method, model, params);
		}
	});

	var SupervisorServiceList = Backbone.Collection.extend({
		model: SupervisorService,

		url: function() {
			return 'http://'+this.server.get("ip") + URL_ROOT + 'service/' + this.server.get("id");
		},

		server: {}
	});

	var SupervisorServiceView = Backbone.View.extend({
		tagName: "div",

		template: _.template($('#service-template').html()),

		initialize: function() {
			_.bindAll(this, 'onModelSaved');
			this.model.bind('change', this.render, this);
			this.model.on('sync', this.onModelSaved)
		},

		onModelSaved: function(model, response, options) {
			if (!model.syncSuccess) {
				this.$el.find(".alert-message").text(model.syncError);
				this.$el.find(".alert").toggle();
			}
		},

		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		},

		events: {
			"click .running_action" : "toggleRunning"
		},

		toggleRunning: function() {
			this.model.toggleRunning();
			if (this.model.get("running")) {
				this.$el.find(".status").text("STARTING");
			} else {
				this.$el.find(".status").text("STOPPING");
			}
		}
	});

	var AppView = Backbone.View.extend({
		el: $("#servers"),

		initialize: function() {
			SupervisorServers.bind('add', this.addOne, this);
			SupervisorServers.bind('reset', this.addAll, this);
			SupervisorServers.bind('all', this.render, this);
			SupervisorServers.fetch();
		},

		addOne: function(server) {console.log('appending server');
			server.fetch();
			var view = new SupervisorServerView({model: server});
			this.$("#server-list").append(view.render().el);
		},

	    addAll: function() {
			SupervisorServers.each(this.addOne);
    	}

	});

	var updateServers = function() {
		SupervisorServers.fetch();
		setTimeout(updateServers, 1000);
	}

	var App = new AppView;

	//updateServers();

});