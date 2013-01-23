drop sequence cms_favourites_favouriteid;
drop sequence cms_posts_postid;
drop table cms_posts;
drop table cms_posts_queue;
drop table cms_users;
drop table cms_user_login_locks;
drop table cms_stats;
drop table cms_favourites;

create table cms_users (
    userid serial primary key,
    username text unique not null,
    userpasswd text,
    useractive boolean default false
);

create table cms_user_login_locks
(
  ip text,
  start_time timestamp,
  end_time timestamp,
  attempts int default 0
);
create index cms_user_login_locks_details on cms_user_login_locks(ip, start_time, end_time);

create table cms_posts (
    postid int not null primary key,
    subject text,
    content text,
    postdate timestamp with time zone,
    modifieddate timestamp with time zone,
    postby int references cms_users(userid)
);

create table cms_posts_queue (
    postid int not null primary key,
    subject text,
    content text,
    postdate timestamp with time zone,
    modifieddate timestamp with time zone,
    postby int references cms_users(userid)
);

create sequence cms_posts_postid;

create table cms_favourites (
    favouriteid int not null primary key,
    postid int,
    imagename text,
    showorder int
);
create index cms_favourites_postid on cms_favourites(postid);

create sequence cms_favourites_favouriteid;

create table cms_stats (
    statid serial not null primary key,
    ip inet,
    url text,
    referer text,
    logtime timestamp with time zone,
    timetaken float,
    querytotal int,
    queryunique int
);

insert into cms_users(username, userpasswd, useractive) values('admin', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', true);

