/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue';

import AdminSettings from './components/AdminSettings';
import {loadState} from '@nextcloud/initial-state'
import Nextcloud from './mixins/Nextcloud';

Vue.mixin(Nextcloud);

const View = Vue.extend(AdminSettings);
new View({
	propsData: {
		stats: loadState('suspicious_login', 'stats')
	}
}).$mount('#suspicious-login-admin-settings');
