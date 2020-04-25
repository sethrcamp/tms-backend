<?php

const DB_HOST = "";
const DB_PORT = "";
const DB_NAME = "";
const DB_USERNAME = "";
const DB_PASSWORD = "";
const DB_DATE_FORMAT = "Y-m-d";
const DB_TIME_FORMAT = "H:i:s";
const DB_DATETIME_FORMAT = "Y-m-d H:i:s";

const DEFAULT_SENDER_EMAIL = "";
const RESET_PASSWORD_REDIRECT_URL = ""; //Must include http:// or https://

const MAX_RESET_PASSWORD_EMAIL_LENGTH_IN_MINUTES = 60 * 24; //24 hours

const SESSION_LENGTH_IN_MINUTES = 60 * 24; //24 hours
const MAX_SESSION_EXTENSION_LENGTH_IN_MINUTES = 60 * 24 * 7;

const DISPLAY_ERRORS = false;

const UNIT_TEST_BASE_URL = "";

const REQUIRE_VALIDATION = true;  //determines whether endpoints are protected by validation
const DEVELOPMENT_USER_ID = null; //used to login as user without the need for credentials
const AUTOMATICALLY_REGENERATE_SESSIONS = false;