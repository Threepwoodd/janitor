Util.Objects["navigationNodes"] = new function() {
	this.init = function(div) {

		div.list = u.qs("ul.nodes", div);

		if(div.list) {

			div.list.update_order_url = div.getAttribute("data-item-order");
			div.list.csrf_token = div.getAttribute("data-csrf-token");
			div.list.nodes = u.qsa("li.item", div.list);


			var i, node;
			for(i = 0; node = div.list.nodes[i]; i++) {

				node.list = div.list;

				// delete button
				var action = u.qs("li.delete", node);

				if(action) {

					form = u.qs("form", action);
					form.node = node;

					// init if form is available
					if(form) {
						u.f.init(form);

						if(u.qs("ul.nodes li.item", node)) {
							u.ac(form.actions["delete"], "disabled");
						}

						form.restore = function(event) {
							this.actions["delete"].value = "Delete";
							u.rc(this.actions["delete"], "confirm");
						}

						form.submitted = function() {

							// first click
							if(!u.hc(this.actions["delete"], "confirm")) {
								u.ac(this.actions["delete"], "confirm");
								this.actions["delete"].value = "Confirm";
								this.t_confirm = u.t.setTimer(this, this.restore, 3000);
							}
							// confirm click
							else {
								u.t.resetTimer(this.t_confirm);


								this.response = function(response) {
									page.notify(response);

									if(response.cms_status == "success") {
	//									location.href = this.cancel_url;
										this.node.parentNode.removeChild(this.node);

										// update
										this.node.list.updateNodeStructure();
									}
								}
								u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
							}
						}
					}
				}
			}

			// node is dropped
			div.list.dropped = function(event) {
//				u.bug("dropped")

				this.updateNodeStructure();
			}


			// save structure and update button states
			div.list.updateNodeStructure = function() {

				var structure = this.getStructure();

				this.response = function(response) {
					page.notify(response);
				}
				u.request(this, this.update_order_url, {"method":"post", "params":"csrf-token="+this.csrf_token+"&structure="+JSON.stringify(structure)});


				var i, node;
				this.nodes = u.qsa("li.item", this);
				for(i = 0; node = this.nodes[i]; i++) {

					// update delete button states
					var action = u.qs("li.delete", node);
					if(action) {
						form = u.qs("form", action);
						if(form) {
							if(u.qs("ul.nodes li.item", node)) {
								u.ac(form.actions["delete"], "disabled");
							}
							else {
								u.rc(form.actions["delete"], "disabled");
							}
						}
					}
				}
			}

			u.sortable(div.list, {"allow_nesting":true, "targets":"nodes", "draggables":"draggable"});

		}

	}
}

// default new form
Util.Objects["newNavigationNode"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		// form.actions["cancel"].clicked = function(event) {
		// 	location.href = this.url;
		// }

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {

					//alert("this.action:" + this.action)
//					alert(response);
					location.href = this.actions["cancel"].url;
//					location.href = this.actions["cancel"].url.replace("\/list", "/edit/"+response.cms_object.item_id);
				}
				else {
					page.notify(response);
				}
			}
//			u.bug("params:"+u.f.getParams(this))
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}

	}
}


Util.Objects["editNavigationNode"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		form.div = div;




		u.f.init(form);
		// form.actions["cancel"].clicked = function(event) {
		// 	location.href = this.url;
		// }
		form.submitted = function(iN) {

			// stop autosave (this could be a manual save)
			u.t.resetTimer(page.t_autosave);

			this.response = function(response) {
				// restart autosave
//				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);

				// notifier will kill autosave if necessary (if login is required)
				// could happen if user log off in other tab
				page.notify(response);

			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}

// 		form.updated = function() {
// //			u.bug("form has been update")
//
// 			this.change_state = true;
// 			u.t.resetTimer(page.t_autosave);
//
// 			if(!page.autosave_disabled) {
//
// //				u.bug("start autosave loop after update")
// 				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
//
// 			}
// 		}

// 		// enable autosaving for testing
// 		form.autosave = function() {
// //			u.bug("autosaving")
//
// 			// is autosave on?
// 			if(!page.autosave_disabled && this.change_state) {
//
// //				u.bug("autosave execute")
//
//
// 				for(name in this.fields) {
// 					if(this.fields[name].field) {
//
// 						// field has not been used yet
// 						if(!this.fields[name].used) {
//
// 							// check for required and value
// 							if(u.hc(this.fields[name].field, "required") && !this.fields[name].val()) {
//
// 								// cannot save due to missing values - keep trying
// //								page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
// 								return false;
// 							}
//
// 						}
// 						// do actual validation
// 						else {
// 							u.f.validate(this.fields[name]);
// 						}
// 					}
// 				}
//
// 				// if error is found after validation
// 				if(!u.qs(".field.error", this)) {
// 					this.change_state = false;
// 					this.submitted();
// 				}
// 				// keep auto save going
// 				// else {
// 				// 	page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
// 				// }
//
// 			}
//
// 			// autosave is turned off
// 			else {
//
//
// 			}
//
// 		}
//
// 		form.change_state = false;
//
// 		page._autosave_node = form;
// 		page._autosave_interval = 3000;
// 		page.t_autosave = u.t.setTimer(form, "autosave", page._autosave_interval);


		// kill backspace to avoid leaving page unintended (backspace is history.back)
		form.cancelBackspace = function(event) {
//			u.bug("ss:" + u.qsa(".field.focus", this).length);
			if(event.keyCode == 8 && !u.qsa(".field.focus").length) {
				u.e.kill(event);
			}
		}
		u.e.addEvent(document.body, "keydown", form.cancelBackspace);

	}
}