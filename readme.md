# Installaction
1. Download this repo to your MediaWiki extension folder

2. Load extension at `LocalSettings.php`
    ```
    wfLoadExtension( 'DiscourseConnect' );
    # replace below with your own detail
    $wgDiscourseConnectSecret = 'development';
    $wgDiscourseConnectEndpoint = 'http://localhost:9292/session/sso_provider';
    ```

3. Run `php maintenance/update.php` to update schema
4. Visit your login page at your MediaWiki instance

# Configuration

## $wgDiscourseConnectSercet

- optional: false
- type: string
- default: null
- eg:

    ```php
    $wgDiscourseConnectSercet='some-secret';
    ```

## $wgDiscoutseConnectEndpoint
The discourse connect provider secret

- optional: false
- type: string
- default: null
- eg:

    ```php
    $wgDiscourseConnectEndpoint='your-discourse-domain/session/sso_provider';
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
        1 => 'admin'
        2 => 'staff'
    ]
    $wgDiscourseConnectUserMapping[1]='admin'
    ```

## Work In Progress

- $wgDiscourseConnectEnableLocalProperites
- $wgDiscourseConnectGroupMapping

# Customize Text

Visit `Special:AllMessages` at your MediaWiki instance then filter using `discourseconnect` prefix.

# TODO

- using Discourse properties(groups, realname, email...)
- update user properties by Discourse WebHook
