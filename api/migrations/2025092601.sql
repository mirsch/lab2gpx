create table adventure_labs
(
    id     INTEGER primary key autoincrement,
    uuid   TEXT not null UNIQUE,
    code   TEXT UNIQUE,
    url    TEXT,
    parent TEXT,
    title  TEXT,
    lat    REAL,
    lon    REAL
);

insert into adventure_labs(id, uuid, code) select id, uuid, lab_code from lab_codes;

create index idx_adventure_labs_uuid on adventure_labs (uuid);
create index idx_adventure_labs_parent on adventure_labs (parent);
create index idx_adventure_labs_code on adventure_labs (code);

drop table lab_codes;
