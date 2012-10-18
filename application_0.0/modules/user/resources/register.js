function isPasswordMatch() {
	if( document.getElementById('password_once').value == document.getElementById('password_twice').value ) {
		document.getElementById('password').value = document.getElementById('password_once').value;
		return true;
	}
	else {
		document.getElementById('password_once').value  = '';
		document.getElementById('password_twice').value = '';
		document.getElementById('password').value       = '';
		document.getElementById('warnings').innerHTML = 'Passwords do not match.  Please re-enter them.';
		return false;
	}
}
function submitFormIfReady() {
	var ready = true;
	if( !isPasswordMatch() ) { ready = false; }
	if( ready ) {
		document.getElementById('profile_edit_form').submit();
	}
	else {
		return false;
	}
}
