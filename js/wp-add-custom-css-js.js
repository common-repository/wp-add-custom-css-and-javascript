var d3_custom_code_editor, d3_custom_code_editor_has_changes = false, d3_custom_css_js_save_publish = false, d3_custom_css_js_rev = 0, d3_custom_css_js_published_rev = 0;
jQuery(document).ready(function($) {
	d3_custom_code_editor = CodeMirror(document.getElementById("d3_custom_code_editor"), {
		lineNumbers: true,
		mode: d3_custom_css_js_mode.toLowerCase(),
		matchBrackets: true
	});
	d3_custom_code_editor.on("change", function() {
		if (d3_custom_code_editor_has_changes)
			return;
		d3_custom_code_editor_has_changes = true;
		$(".d3-custom-css-js-save-btn").html("Save").prop("disabled", false);
		$(".d3-custom-css-js-publish-btn").html("Save &amp; Publish").prop("disabled", false);
	});
	$(".d3-custom-css-js-save-btn").click(function() {
		$(".d3-custom-css-js-save-btn").prop("disabled", true).html("Saving...");
		$.post(ajaxurl, {action: "d3_custom_css_js_save", mode: d3_custom_css_js_mode, code: d3_custom_code_editor.getValue()})
			.done(function(data) {
				if (data.success) {
					$(".d3-custom-css-js-save-btn").html("Saved");
					d3_custom_css_js_rev = data.data;
					d3_custom_code_editor_has_changes = false;
					if (d3_custom_css_js_save_publish)
						$(".d3-custom-css-js-publish-btn").click()
					else
						d3_custom_css_js_get_revisions();
				} else {
					alert("Error while saving. Please try again.");
					$(".d3-custom-css-js-save-btn").html("Save").prop("disabled", false);
					if (d3_custom_css_js_save_publish)
						$(".d3-custom-css-js-publish-btn").html("Save &amp; Publish").prop("disabled", true);
				}
			})
			.fail(function() {
				alert("Error while saving. Please try again.");
					$(".d3-custom-css-js-save-btn").html("Save").prop("disabled", false);
					if (d3_custom_css_js_save_publish)
						$(".d3-custom-css-js-publish-btn").html("Save &amp; Publish").prop("disabled", true);
			});
	});
	$(".d3-custom-css-js-publish-btn").click(function() {
		$(".d3-custom-css-js-publish-btn").prop("disabled", true).html("Publishing...");
		if (!$(".d3-custom-css-js-save-btn").prop("disabled")) {
			d3_custom_css_js_save_publish = true;
			$(".d3-custom-css-js-save-btn").click();
			return;
		}
		d3_custom_css_js_save_publish = false;
		
		$.post(ajaxurl, {action: "d3_custom_css_js_publish", mode: d3_custom_css_js_mode, minify: ($('.d3-custom-css-js-minify-cb').prop('checked') ? 1 : 0), rev: d3_custom_css_js_rev})
			.done(function(data) {
				if (data.success) {
					$(".d3-custom-css-js-publish-btn").html("Published");
					d3_custom_css_js_get_revisions();
				} else {
					alert("Error while publishing. Please try again.");
					$(".d3-custom-css-js-publish-btn").html("Save &amp; Publish").prop("disabled", false);
				}
			})
			.fail(function() {
				alert("Error while publishing. Please try again.");
				$(".d3-custom-css-js-publish-btn").html("Save &amp; Publish").prop("disabled", false);
			});
	});
	
	$(".d3-custom-css-js-delete-revisions-btn").click(function() {
		$(this).prop('disabled', true).html('Deleting...');
		
		$.post(ajaxurl, {action: "d3_custom_css_js_delete_revisions", mode: d3_custom_css_js_mode})
			.done(function(data) {
				if (data.success) {
					d3_custom_css_js_get_revisions();
					$(".d3-custom-css-js-delete-revisions-btn").html('Delete All').prop('disabled', false);	
				} else {
					alert("Error while deleting. Please try again.");
					$(".d3-custom-css-js-delete-revisions-btn").html('Delete All').prop('disabled', false);
				}
			})
			.fail(function() {
				alert("Error while deleting. Please try again.");
				$(".d3-custom-css-js-delete-revisions-btn").html('Delete All').prop('disabled', false);
			});
	});
	
	
	$(window).resize(function() {
		$("#d3_custom_code_editor, #d3_custom_code_editor .CodeMirror").height(Math.max(150,
														$(window).height()
														- $("#d3_custom_code_editor").offset().top
														- $(".d3-custom-css-js-save-btn").height()
														- 30));
		d3_custom_code_editor.refresh();
	});
	$(window).resize();
	$(window).on("beforeunload", function(ev) {
		if (d3_custom_code_editor_has_changes) {
			ev.returnValue = "You have unsaved changes that will be lost if you leave this page!";
			return ev.returnValue;
		}
	});
	
	$("#d3_custom_css_js_revisions").on("click", "li > a.view-rev", function(ev) {
		
		if (d3_custom_code_editor_has_changes &&
				!confirm("You have unsaved changes that will be lost if you view this revision!"))
			return;
		
		var revId = $(this).parent().attr("id").substring(20);
		
		$.post(ajaxurl, {action: "d3_custom_css_js_get_revision", mode: d3_custom_css_js_mode, rev: revId})
			.done(function(data) {
				if (data.success) {
					d3_custom_code_editor.doc.setValue(data.data.content);
					d3_custom_css_js_rev = data.data.id;
					$('#d3_custom_css_js_revisions .active').removeClass('active');
					$('#d3_custom_css_js_rev' + d3_custom_css_js_rev).addClass('active');
					$(".d3-custom-css-js-save-btn").html("Saved").prop("disabled", true);
					if (d3_custom_css_js_rev == d3_custom_css_js_published_rev)
						$(".d3-custom-css-js-publish-btn").html("Published").prop("disabled", true);
					d3_custom_code_editor_has_changes = false;
				} else {
					alert("Error while loading. Please try again.");
				}
			})
			.fail(function() {
				alert("Error while loading. Please try again.");
			});
	});
	
	$("#d3_custom_css_js_revisions").on("click", "li > a.del-rev", function(ev) {
		
		var revId = $(this).parent().attr("id").substring(20);
		
		$.post(ajaxurl, {action: "d3_custom_css_js_delete_revision", mode: d3_custom_css_js_mode, rev: revId})
			.done(function(data) {
				if (data.success) {
					d3_custom_css_js_get_revisions();
				} else {
					alert("Error while deleting. Please try again.");
				}
			})
			.fail(function() {
				alert("Error while deleting. Please try again.");
			});
	});
	
	function d3_custom_css_js_get_revisions() {
		$.post(ajaxurl, {action: "d3_custom_css_js_get_revisions", mode: d3_custom_css_js_mode, })
				.done(function(data) {
					if (data.success) {
						$("#d3_custom_css_js_revisions").empty();
						if (data.data.length == 0) {
							$("#d3_custom_css_js_revisions").append("<li>None</li>");
						} else {
							for (var i = 0; i < data.data.length; ++i) {
								$("#d3_custom_css_js_revisions").append("<li id=\"d3_custom_css_js_rev" + data.data[i].id + "\"><a class=\"view-rev\" href=\"javascript:void(0);\">" + data.data[i].rev_date + "</a>" + (data.data[i].published ? " [published]" : " <a class=\"del-rev\" href=\"javascript:void(0);\">[delete]</a>") + "</li>");
								if (data.data[i].published)
									d3_custom_css_js_published_rev = data.data[i].id;
							}
							if (d3_custom_css_js_rev == 0) {
								$("#d3_custom_css_js_revisions > li:first-child > a.view-rev").click();
							} else {
								$('#d3_custom_css_js_rev' + d3_custom_css_js_rev).addClass('active');
							}
						}
					}
				});
	}
	d3_custom_css_js_get_revisions();
	
});