drop sequence cms_favourites_favouriteid;
drop sequence cms_posts_postid;
drop sequence cms_comments_commentid;
drop table cms_posts;
drop table cms_posts_queue;
drop table cms_users;
drop table cms_user_login_locks;
drop table cms_stats;
drop table cms_favourites;
drop table cms_comments;
drop table cms_comments_queue;

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
    postid int,
    imagename text,
    showorder int
);
alter table cms_favourites add constraint cms_favourites_pkey primary key (postid, imagename);

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

create table cms_comments (
    commentid int not null primary key,
    content text,
    commentemail text,
    commentby text,
    commentdate timestamp with time zone,
    modifieddate timestamp with time zone,
    postid int not null
);

create table cms_comments_queue (
    commentid int not null primary key,
    content text,
    commentemail text,
    commentby text,
    commentdate timestamp with time zone,
    modifieddate timestamp with time zone,
    postid int not null
);

create sequence cms_comments_commentid;

insert into cms_users(username, userpasswd, useractive) values('admin', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', true);

