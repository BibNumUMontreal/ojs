<?php

/**
 * @file controllers/grid/admin/plugins/AdminPluginGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPluginGridHandler
 * @ingroup controllers_grid_admin_plugins
 *
 * @brief Handle site level plugins grid requests.
 */

import('lib.pkp.classes.controllers.grid.plugins.PluginGridHandler');

class AdminPluginGridHandler extends PluginGridHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		$roles = array(ROLE_ID_SITE_ADMIN);

		$this->addRoleAssignment($roles, array('plugin'));

		parent::__construct($roles);
	}

	//
	// Overriden template methods.
	//
	/**
	 * @copydoc GridHandler::getRowInstance()
	 */
	protected function getRowInstance() {
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		import('controllers.grid.plugins.PluginGridRow');
		return new PluginGridRow($userRoles, CONTEXT_JOURNAL);
	}

	/**
	 * @copydoc GridHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		$category = $request->getUserVar('category');
		$pluginName = $request->getUserVar('plugin');
		$verb = $request->getUserVar('verb');

		if ($category && $pluginName) {
			import('classes.security.authorization.OjsPluginAccessPolicy');
			if ($verb) {
				$accessMode = ACCESS_MODE_MANAGE;
			} else {
				$accessMode = ACCESS_MODE_ADMIN;
			}

			$this->addPolicy(new OjsPluginAccessPolicy($request, $args, $roleAssignments, $accessMode));
		} else {
			import('lib.pkp.classes.security.authorization.PolicySet');
			$rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

			import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
			foreach($roleAssignments as $role => $operations) {
				$rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
			}
			$this->addPolicy($rolePolicy);
		}

		return parent::authorize($request, $args, $roleAssignments);
	}
}

?>