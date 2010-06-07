$(function() {

	var PackageList = {

		initialize: function(list) {
			this.list = list;
		},

		add: function(key, value) {
			var li = $("<li/>").attr("id", "package-" + key);
			var strong = $("<strong/>").html(value);
			li.append(strong);
			this.list.append(li);
		},

		remove: function(row){
		}

	};

	this.PackageSelecter = {

		initialize: function(pulldown, button){
			this.noSelectPackage = false;
			this.pulldown = pulldown;
			this.button = button;
			this.items = $(this.pulldown).children('option');
			$(this.button).click($.proxy(this.addPackage, this));
		},

		addPackage: function(event) {
			event.preventDefault();
			if (!this.noSelectPackage) {
				var items = $(this.pulldown).children('option');
				var item = $(this.pulldown).val();
				var curent = $(this.pulldown).find('option[value=' + item + ']');
				$.proxy(PackageList.add, PackageList)(item, PluginPackages[item]);
				curent.remove();
	
				if (items.length <= 1) {
					var option = $("<option/>").val("").html("not package");
					$(this.pulldown).append(option);
					$(this.pulldown).attr("disable", "disable");
					this.noSelectPackage = true;
				}
			}
		}
	};
	PackageList.initialize.apply(PackageList, [$("#packageList")]);
	this.PackageSelecter.initialize.apply(this.PackageSelecter, [$("#package"), $("#addFileset")]);
	
});
