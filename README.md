# com.megaphonetech.monitoring

This extension enhances the built-in CiviCRM system checks.

Currently, it allows you to do remote system checks without granting "Administer CiviCRM" permissions to your monitoring user.  In the future, additional system checks are planned.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.megaphonetech.monitoring@https://github.com/MegaphoneJon/com.megaphonetech.monitoring/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/MegaphoneJon/com.megaphonetech.monitoring.git
cv en monitoring
```

## Usage

After installing, you'll have a new permission "CiviCRM Remote Monitoring".  Grant this to a monitoring user if you want to conduct remote checks (using `System.check` API) without granting "Administer CiviCRM` privileges" to your monitoring system.
