CREATE TABLE IF NOT EXISTS /*_*/discourse_user (
    discourse_external_id int unsigned NOT NULL,
    mediawiki_user_id int unsigned,
    PRIMARY KEY (discourse_external_id),
    FOREIGN KEY (mediawiki_user_id) REFERENCES /*_*/user(user_id)
)/*$wgDBTableOptions*/;