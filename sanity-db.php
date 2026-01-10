<?php

// Lets check to see if we have a database or not and give a decent error message
try {
    define('PDO_WILL_CATCH', true);
    require_once("pdo.php");
} catch(PDOException $ex){
    $msg = $ex->getMessage();
    error_log("DB connection: ".$msg);
    echo('<div class="alert alert-danger" style="margin: 10px;">'."\n");
    if ( strpos($msg, 'Unknown database') !== false ) {
        echo("<p>It does not appear as though your database exists.</p>
<p> If you have full access to your MySql instance (i.e. like 
MAMP or XAMPP, you may need to run commands like this:</p>
<pre>
    CREATE DATABASE IF NOT EXISTS mmorps DEFAULT CHARACTER SET utf8;
    CREATE USER IF NOT EXISTS 'mmouser'@'localhost' IDENTIFIED BY 'mmopassword';
    CREATE USER IF NOT EXISTS 'mmouser'@'127.0.0.1' IDENTIFIED BY 'mmopassword';
    GRANT ALL ON mmorps.* TO 'mmouser'@'localhost';
    GRANT ALL ON mmorps.* TO 'mmouser'@'127.0.0.1';
    FLUSH PRIVILEGES;
</pre>
<p>Make sure to choose appropriate passwords when setting this up.</p>
<p>If you are running in a hosted environment and are using an admin tool like
CPanel (or equivalent).  You must user this interface to create a database, 
user, and password.</p>
<p>
In some systems, a database adminstrator will create the database,
user, and password and simply give them to you.
<p>
Once you have the database, account and password you must update your
<code>config.php</code> with this information.</p>
");
    } else if ( strpos($msg, 'Access denied for user') !== false ) {
        echo('<p>It appears that you are unable to access 
your database due to a problem with the user and password.
The user and password for the database conneciton are setup using either a 
SQL <code>CREATE USER</code> and <code>GRANT</code> commands or created in an adminstration tool like CPanel.
Here are sample commands to set up a database:'."
<pre>
    CREATE DATABASE IF NOT EXISTS mmorps DEFAULT CHARACTER SET utf8;
    CREATE USER IF NOT EXISTS 'mmouser'@'localhost' IDENTIFIED BY 'mmopassword';
    CREATE USER IF NOT EXISTS 'mmouser'@'127.0.0.1' IDENTIFIED BY 'mmopassword';
    GRANT ALL ON mmorps.* TO 'mmouser'@'localhost';
    GRANT ALL ON mmorps.* TO 'mmouser'@'127.0.0.1';
    FLUSH PRIVILEGES;
</pre>".'
Or perhaps a system administrator created the database and gave you the
account and password to access the database.</p>
<p>Make sure to check the values in your <code>config.php</code> for 
<pre>
    $CFG->dbuser    = \'mmouser\';
    $CFG->dbpass    = \'mmopassword\';
</pre>
To make sure they match the account and password assigned to your database.
</p>
');
    } else if ( strpos($msg, 'Can\'t connect to MySQL server') !== false ) {
        echo('<p>It appears that you cannot connect to your MySQL server at 
all.  The most likely problem is the wrong host or port in this option 
in your <code>config.php</code> file:
<pre>
$CFG->pdo       = \'mysql:host=127.0.0.1;dbname=mmorps\';
# $CFG->pdo       = \'mysql:host=127.0.0.1;port=8889;dbname=mmorps\'; // MAMP
</pre>
The host may be incorrect - you might try switching from \'127.0.0.1\' to 
\'localhost\'.   Or if you are on a hosted system with an ISP the name of the 
database host might be given to you like \'db4263.mysql.1and1.com\' and you 
need to put that host name in the PDO string.</p>
<p>
Most systems are configured to use the default MySQL port of 3306 and if you 
omit "port=" in the PDO string it assumes 3306.  If you are using MAMP
this is usually moved to port 8889.  If neither 3306 nor 8889 works you
probably have a bad host name.  Or talk to your system administrator.
</p>
');
    } else {
        echo("<p>There is a problem with your database connection.</p>\n");
    }

    echo("<p>Database error detail: ".$msg."</p>\n");
    echo("<p>Once you have fixed the problem, come back to this page and refresh
to see if this message goes away.</p>");
    echo('<p>Installation instructions are avaiable at 
<a href="https://github.com/csev/mmorps"
target="_blank">https://github.com/csev/mmorps</a>');

    echo("\n</div>\n");
    die();
}   

// Check to see if the data tables have been created
$p = $CFG->dbprefix;
$table_fields = pdoMetadata($pdo, "{$p}user");
if ( $table_fields === false ) {
    error_log("Creating user table");
    echo('<div class="alert alert-danger" style="margin: 10px;">'."\n");
    echo("<p>Creating user table</p>\n");
    pdoQueryDie($pdo, 
"create table {$CFG->dbprefix}user (
    user_id             INTEGER NOT NULL AUTO_INCREMENT,
    user_sha256         CHAR(64) NOT NULL,
    user_key            VARCHAR(4096) NOT NULL,

    admin               SMALLINT,

    displayname         VARCHAR(2048) NULL,
    email               VARCHAR(2048) NULL,
    locale              CHAR(63) NULL,

    map                 SMALLINT,
    lat                 FLOAT NULL,
    lng                 FLOAT NULL,

    homepage            VARCHAR(1024) NULL,
    blog                VARCHAR(1024) NULL,
    avatar              VARCHAR(1024) NULL,
    avatarlink          VARCHAR(1024) NULL,

    json                TEXT NULL,
    provider            VARCHAR(50) NULL,
    login_at            DATETIME NOT NULL,
    login_ip            VARCHAR(999) NOT NULL,
    created_at          DATETIME NOT NULL,
    updated_at          DATETIME NOT NULL,
    wins                INTEGER DEFAULT 0,
    losses              INTEGER DEFAULT 0,

    PRIMARY KEY (user_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8");
    echo("\n</div>\n");
}

// Check if provider column exists, add it if missing (for existing installations)
$table_fields = pdoMetadata($pdo, "{$p}user");
if ($table_fields !== false) {
    $has_provider = false;
    $has_wins = false;
    $has_losses = false;
    foreach ($table_fields as $field) {
        if (isset($field['Field']) && $field['Field'] === 'provider') {
            $has_provider = true;
        }
        if (isset($field['Field']) && $field['Field'] === 'wins') {
            $has_wins = true;
        }
        if (isset($field['Field']) && $field['Field'] === 'losses') {
            $has_losses = true;
        }
    }
    if (!$has_provider) {
        error_log("Adding provider column to user table");
        pdoQueryDie($pdo, "ALTER TABLE {$CFG->dbprefix}user ADD COLUMN provider VARCHAR(50) NULL AFTER json");
    }
    if (!$has_wins) {
        error_log("Adding wins column to user table");
        pdoQueryDie($pdo, "ALTER TABLE {$CFG->dbprefix}user ADD COLUMN wins INTEGER DEFAULT 0 AFTER updated_at");
    }
    if (!$has_losses) {
        error_log("Adding losses column to user table");
        pdoQueryDie($pdo, "ALTER TABLE {$CFG->dbprefix}user ADD COLUMN losses INTEGER DEFAULT 0 AFTER wins");
    }
}

$table_fields = pdoMetadata($pdo, "{$p}rps");
if ( $table_fields === false ) {
    error_log("Creating rps table");
    echo('<div class="alert alert-danger" style="margin: 10px;">'."\n");
    echo("<p>Creating rps table</p>\n");
    pdoQueryDie($pdo, 
"create table {$CFG->dbprefix}rps (
    rps_guid    VARCHAR(64) NOT NULL,
    user1_id    INTEGER NOT NULL,
    play1       INTEGER NOT NULL,
    user2_id    INTEGER,
    play2       INTEGER,
    started_at  DATETIME NOT NULL,
    finished_at DATETIME,

    CONSTRAINT `{$CFG->dbprefix}rps_ibfk_2`
        FOREIGN KEY (`user1_id`)
        REFERENCES `{$CFG->dbprefix}user` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT `{$CFG->dbprefix}rps_ibfk_3`
        FOREIGN KEY (`user2_id`)
        REFERENCES `{$CFG->dbprefix}user` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `{$CFG->dbprefix}rps_indx_1` USING HASH (`rps_guid`),
    UNIQUE(rps_guid)
) ENGINE = InnoDB DEFAULT CHARSET=utf8");
    echo("\n</div>\n");
}

$table_fields = pdoMetadata($pdo, "{$p}pair");
if ( $table_fields === false ) {
    error_log("Creating pair table");
    echo('<div class="alert alert-danger" style="margin: 10px;">'."\n");
    echo("<p>Creating pair table</p>\n");
    pdoQueryDie($pdo, 
"create table {$CFG->dbprefix}pair (
    pair_id     INTEGER NOT NULL AUTO_INCREMENT,
    pair_key    INTEGER NULL,
    pair_guid   VARCHAR(64) NOT NULL,
    user_id     INTEGER NOT NULL,
    created_at  DATETIME NOT NULL,
    paired_at   DATETIME,

    CONSTRAINT `{$CFG->dbprefix}pair_ibfk_2`
        FOREIGN KEY (`user_id`)
        REFERENCES `{$CFG->dbprefix}user` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `{$CFG->dbprefix}pair_indx_1` USING HASH (`pair_guid`),

    UNIQUE(pair_guid),
    UNIQUE(pair_key),
    PRIMARY KEY (pair_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8");
    echo("\n</div>\n");
}

