function isPasswordMatch() {
	if( document.getElementById('super_password_once').value == document.getElementById('super_password_twice').value ) {
		document.getElementById('super_password').value = document.getElementById('super_password_once').value;
		return true;
	}
	else {
		document.getElementById('super_password_once').value  = '';
		document.getElementById('super_password_twice').value = '';
		document.getElementById('super_password').value       = '';
		document.getElementById('warnings').innerHTML = 'Passwords do not match.  Please re-enter them.';
		return false;
	}
}

function submitFormIfReady() {
	var ready = true;
	if( !isPasswordMatch() ) { ready = false; }
	if( ready ) {
		document.getElementById('user_initialize_form').submit();
	}
	else {
		return false;
	}
}

