SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;


CREATE SCHEMA system;


SET search_path = system, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;


CREATE TABLE audit_trail (
    audit_trail_id integer NOT NULL,
    user_id integer NOT NULL,
    item_id integer NOT NULL,
    item_type character varying(64) NOT NULL,
    description character varying(4000) NOT NULL,
    audit_date timestamp without time zone NOT NULL,
    type numeric NOT NULL,
    data text
);


CREATE SEQUENCE audit_trail_audit_trail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER SEQUENCE audit_trail_audit_trail_id_seq OWNED BY audit_trail.audit_trail_id;


CREATE TABLE audit_trail_data (
    audit_trail_data_id integer NOT NULL,
    audit_trail_id integer NOT NULL,
    data text
);


CREATE SEQUENCE audit_trail_data_audit_trail_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER SEQUENCE audit_trail_data_audit_trail_data_id_seq OWNED BY audit_trail_data.audit_trail_data_id;


CREATE TABLE permissions (
    permission_id integer NOT NULL,
    role_id integer NOT NULL,
    permission character varying(4000),
    value numeric NOT NULL,
    module character varying(4000)
);


CREATE SEQUENCE permissions_permission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER SEQUENCE permissions_permission_id_seq OWNED BY permissions.permission_id;


CREATE TABLE roles (
    role_id integer NOT NULL,
    role_name character varying(64)
);


CREATE SEQUENCE roles_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER SEQUENCE roles_role_id_seq OWNED BY roles.role_id;


CREATE TABLE users (
    user_id integer NOT NULL,
    user_name character varying(64) NOT NULL,
    password character varying(64) NOT NULL,
    role_id integer,
    first_name character varying(64) NOT NULL,
    last_name character varying(64) NOT NULL,
    other_names character varying(64),
    user_status numeric(1,0),
    email character varying(64) NOT NULL
);


CREATE SEQUENCE users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER SEQUENCE users_user_id_seq OWNED BY users.user_id;


ALTER TABLE audit_trail ALTER COLUMN audit_trail_id SET DEFAULT nextval('audit_trail_audit_trail_id_seq'::regclass);


ALTER TABLE audit_trail_data ALTER COLUMN audit_trail_data_id SET DEFAULT nextval('audit_trail_data_audit_trail_data_id_seq'::regclass);


ALTER TABLE permissions ALTER COLUMN permission_id SET DEFAULT nextval('permissions_permission_id_seq'::regclass);


ALTER TABLE roles ALTER COLUMN role_id SET DEFAULT nextval('roles_role_id_seq'::regclass);


ALTER TABLE users ALTER COLUMN user_id SET DEFAULT nextval('users_user_id_seq'::regclass);


ALTER TABLE ONLY audit_trail
    ADD CONSTRAINT audit_trail_audit_id_pk PRIMARY KEY (audit_trail_id);


ALTER TABLE ONLY audit_trail_data
    ADD CONSTRAINT audit_trail_data_id_pk PRIMARY KEY (audit_trail_data_id);


ALTER TABLE ONLY permissions
    ADD CONSTRAINT perm_id_pk PRIMARY KEY (permission_id);


ALTER TABLE ONLY roles
    ADD CONSTRAINT role_id_pk PRIMARY KEY (role_id);


ALTER TABLE ONLY users
    ADD CONSTRAINT user_id_pk PRIMARY KEY (user_id);


ALTER TABLE ONLY users
    ADD CONSTRAINT user_name_uk UNIQUE (user_name);


ALTER TABLE ONLY audit_trail
    ADD CONSTRAINT audit_trail_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;


ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissios_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE;


ALTER TABLE ONLY users
    ADD CONSTRAINT users_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL;


CREATE TABLE system.api_keys
(
  api_key_id serial NOT NULL,
  user_id integer NOT NULL,
  active boolean NOT NULL,
  key character varying(512) NOT NULL,
  secret character varying(512) NOT NULL,
  CONSTRAINT api_keys_pkey PRIMARY KEY (api_key_id ),
  CONSTRAINT api_keys_user_id_fkey FOREIGN KEY (user_id)
      REFERENCES system.users (user_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE system.notes
(
  note_id serial NOT NULL,
  note character varying(4000),
  note_time timestamp without time zone NOT NULL,
  item_id integer NOT NULL,
  item_type character varying(1024) NOT NULL,
  user_id integer NOT NULL,
  CONSTRAINT notes_note_id_pk PRIMARY KEY (note_id),
  CONSTRAINT notes_user_id_fk FOREIGN KEY (user_id)
      REFERENCES system.users (user_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);