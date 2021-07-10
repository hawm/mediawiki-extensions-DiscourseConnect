A MediaWiki extension that implemented DiscourseConnect consumer, allows your MediaWiki instance login with Discourse account, and more features are working in progress.

# Installation

1. Download this repo in a directory called `DiscourseConnect` in the MediaWiki extensions folder
2. Load extension in `LocalSettings.php`

   ```php
   wfLoadExtension( 'DiscourseConnect' );
   # replace below values with your own detail
   $wgDiscourseConnectSecret = 'some-secret';
   $wgDiscourseConnectEndpoint = 'https://your.discourse.domain/session/sso_provider';
   ```

3. Run `php maintenance/update.php` to update extensions schema
4. All done

# Configuration

## $wgDiscourseConnectSercet

The `discourse connect provider secrets` setting from Discourse

- optional: false
- type: string
- default: null
- eg:

  ```php
  $wgDiscourseConnectSercet='some-secret';
  ```

## $wgDiscoutseConnectEndpoint

The DiscourseConnect endpoint of your Discourse instance

- optional: false
- type: string
- default: null
- eg:

  ```php
  $wgDiscourseConnectEndpoint='https://your.discourse.domain/session/sso_provider';
  ```

## $wgDiscourseConnectEnableLocalLogin

Enable local password login, local password login will be disable by default when enable this extension.

- optional: true
- type: boolean
- default: false
- eg:

  ```php
  $wgDiscourseConnectEnableLocalLogin=true;
  ```

## $wgDiscourseConnectUserMapping

Mapping user between Discourse to Mediawiki.

- optional: true
- type: array
- default: null
- eg:

  ```php
  $wgDiscourseConnectUserMapping=[
      // discourse_id => mediawiki_username
      1 => 'admin',
      2 => 'staff'
  ]
  $wgDiscourseConnectUserMapping[3]='moderator'
  ```

## Work In Progress

- `$wgDiscourseConnectEnableLocalProperites` populate Discourse properties
- `$wgDiscourseConnectGroupMapping` mapping group between Discourse and MediaWiki

# Customize Text

Visit `Special:AllMessages` at your MediaWiki instance then filter using `discourseconnect` prefix.

# TODO

- Populate Discourse properties(groups, realname, email...)
- Accept Discourse WebHook to update properties
- Add unite tests
- Replace the default talk function of MediaWiki
