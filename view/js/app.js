//アンカー
function sub_redirect(base, con, met, pars) {

	var param = base + con;

	if (met != '' && met != undefined) {
		param += '/' + met;
	}

	if (pars != '' && pars != undefined) {
		param += '/' + pars;
	}

	location.href = param;
}

function sub_module(mod, proc) {

	var frm = document.inputform;

	if (sub_proc(proc) == false) return false;

	var act = frm.getAttribute('action');
	act += mod;

	frm.setAttribute('action', act);
	
	frm.submit();
}

function sub_proc(proc) {

	var msg = proc;
	
	msg += "します。よろしいですか？";
	if (!window.confirm(msg)) return false;

	return true;
}