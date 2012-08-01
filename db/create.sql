drop table cms_users;
drop table cms_user_login_locks;
drop table cms_posts;
drop table cms_posts_queue;

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
    postid serial primary key,
    subject text,
    content text,
    postdate timestamp with time zone,
    postby int references cms_users(userid)
);

create table cms_posts_queue (
    queueid serial primary key,
    postid int,
    subject text,
    content text,
    postdate timestamp with time zone,
    postby int references cms_users(userid)
);

insert into cms_users(username, userpasswd, useractive) values('admin', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', true);

