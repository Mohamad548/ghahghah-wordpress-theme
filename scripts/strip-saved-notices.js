const fs = require('fs');
const path = require('path');
const dir = path.join('ghahghah-theme', 'inc', 'admin', 'panels');
const re =
	/\nif \( isset\( \$_GET\['ghahghah_saved'\] \) \) \{ \/\/ phpcs:ignore WordPress\.Security\.NonceVerification\.Recommended\n\techo '<div class="notice notice-success is-dismissible"><p>';\n\tesc_html_e\( '[^']+', 'ghahghah' \);\n\techo '<\/p><\/div>';\n\}\n/g;
for (const name of fs.readdirSync(dir)) {
	if (!name.endsWith('.php')) continue;
	const file = path.join(dir, name);
	const text = fs.readFileSync(file, 'utf8');
	const next = text.replace(re, '\n');
	if (next !== text) {
		fs.writeFileSync(file, next);
		console.log('cleaned', name);
	} else {
		console.log('skip', name);
	}
}
