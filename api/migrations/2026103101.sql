-- adventure labs failing in labs-api (e.g. status 500 with "Lodestone" error), skipped for a while to not run into the error on every request
create table if not exists adventure_lab_errors
(
    uuid           TEXT primary key,
    error_code     INTEGER not null,
    error_count    INTEGER not null default 1,
    first_error_at INTEGER not null,
    last_error_at  INTEGER not null
);
