--
-- PostgreSQL database dump
--

-- Started on 2012-03-27 07:52:02 GMT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 7 (class 2615 OID 1206352)
-- Name: system; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA system;


SET search_path = system, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1512 (class 1259 OID 1206353)
-- Dependencies: 7
-- Name: audit_trail; Type: TABLE; Schema: system; Owner: -; Tablespace: 
--

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


--
-- TOC entry 1513 (class 1259 OID 1206359)
-- Dependencies: 7 1512
-- Name: audit_trail_audit_trail_id_seq; Type: SEQUENCE; Schema: system; Owner: -
--

CREATE SEQUENCE audit_trail_audit_trail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1823 (class 0 OID 0)
-- Dependencies: 1513
-- Name: audit_trail_audit_trail_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: -
--

ALTER SEQUENCE audit_trail_audit_trail_id_seq OWNED BY audit_trail.audit_trail_id;


--
-- TOC entry 1514 (class 1259 OID 1206361)
-- Dependencies: 7
-- Name: audit_trail_data; Type: TABLE; Schema: system; Owner: -; Tablespace: 
--

CREATE TABLE audit_trail_data (
    audit_trail_data_id integer NOT NULL,
    audit_trail_id integer NOT NULL,
    data text
);


--
-- TOC entry 1515 (class 1259 OID 1206367)
-- Dependencies: 7 1514
-- Name: audit_trail_data_audit_trail_data_id_seq; Type: SEQUENCE; Schema: system; Owner: -
--

CREATE SEQUENCE audit_trail_data_audit_trail_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1824 (class 0 OID 0)
-- Dependencies: 1515
-- Name: audit_trail_data_audit_trail_data_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: -
--

ALTER SEQUENCE audit_trail_data_audit_trail_data_id_seq OWNED BY audit_trail_data.audit_trail_data_id;


--
-- TOC entry 1516 (class 1259 OID 1206369)
-- Dependencies: 7
-- Name: permissions; Type: TABLE; Schema: system; Owner: -; Tablespace: 
--

CREATE TABLE permissions (
    permission_id integer NOT NULL,
    role_id integer NOT NULL,
    permission character varying(4000),
    value numeric NOT NULL,
    module character varying(4000)
);


--
-- TOC entry 1517 (class 1259 OID 1206375)
-- Dependencies: 7 1516
-- Name: permissions_permission_id_seq; Type: SEQUENCE; Schema: system; Owner: -
--

CREATE SEQUENCE permissions_permission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1825 (class 0 OID 0)
-- Dependencies: 1517
-- Name: permissions_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: -
--

ALTER SEQUENCE permissions_permission_id_seq OWNED BY permissions.permission_id;


--
-- TOC entry 1518 (class 1259 OID 1206377)
-- Dependencies: 7
-- Name: roles; Type: TABLE; Schema: system; Owner: -; Tablespace: 
--

CREATE TABLE roles (
    role_id integer NOT NULL,
    role_name character varying(64)
);


--
-- TOC entry 1519 (class 1259 OID 1206380)
-- Dependencies: 7 1518
-- Name: roles_role_id_seq; Type: SEQUENCE; Schema: system; Owner: -
--

CREATE SEQUENCE roles_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1826 (class 0 OID 0)
-- Dependencies: 1519
-- Name: roles_role_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: -
--

ALTER SEQUENCE roles_role_id_seq OWNED BY roles.role_id;


--
-- TOC entry 1520 (class 1259 OID 1206382)
-- Dependencies: 7
-- Name: users; Type: TABLE; Schema: system; Owner: -; Tablespace: 
--

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


--
-- TOC entry 1521 (class 1259 OID 1206385)
-- Dependencies: 7 1520
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: system; Owner: -
--

CREATE SEQUENCE users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1827 (class 0 OID 0)
-- Dependencies: 1521
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: -
--

ALTER SEQUENCE users_user_id_seq OWNED BY users.user_id;


--
-- TOC entry 1801 (class 2604 OID 1206387)
-- Dependencies: 1513 1512
-- Name: audit_trail_id; Type: DEFAULT; Schema: system; Owner: -
--

ALTER TABLE audit_trail ALTER COLUMN audit_trail_id SET DEFAULT nextval('audit_trail_audit_trail_id_seq'::regclass);


--
-- TOC entry 1802 (class 2604 OID 1206388)
-- Dependencies: 1515 1514
-- Name: audit_trail_data_id; Type: DEFAULT; Schema: system; Owner: -
--

ALTER TABLE audit_trail_data ALTER COLUMN audit_trail_data_id SET DEFAULT nextval('audit_trail_data_audit_trail_data_id_seq'::regclass);


--
-- TOC entry 1803 (class 2604 OID 1206389)
-- Dependencies: 1517 1516
-- Name: permission_id; Type: DEFAULT; Schema: system; Owner: -
--

ALTER TABLE permissions ALTER COLUMN permission_id SET DEFAULT nextval('permissions_permission_id_seq'::regclass);


--
-- TOC entry 1804 (class 2604 OID 1206390)
-- Dependencies: 1519 1518
-- Name: role_id; Type: DEFAULT; Schema: system; Owner: -
--

ALTER TABLE roles ALTER COLUMN role_id SET DEFAULT nextval('roles_role_id_seq'::regclass);


--
-- TOC entry 1805 (class 2604 OID 1206391)
-- Dependencies: 1521 1520
-- Name: user_id; Type: DEFAULT; Schema: system; Owner: -
--

ALTER TABLE users ALTER COLUMN user_id SET DEFAULT nextval('users_user_id_seq'::regclass);


--
-- TOC entry 1807 (class 2606 OID 1206393)
-- Dependencies: 1512 1512
-- Name: audit_trail_audit_id_pk; Type: CONSTRAINT; Schema: system; Owner: -; Tablespace: 
--

ALTER TABLE ONLY audit_trail
    ADD CONSTRAINT audit_trail_audit_id_pk PRIMARY KEY (audit_trail_id);


--
-- TOC entry 1809 (class 2606 OID 1206395)
-- Dependencies: 1514 1514
-- Name: audit_trail_data_id_pk; Type: CONSTRAINT; Schema: system; Owner: -; Tablespace: 
--

ALTER TABLE ONLY audit_trail_data
    ADD CONSTRAINT audit_trail_data_id_pk PRIMARY KEY (audit_trail_data_id);


--
-- TOC entry 1811 (class 2606 OID 1206397)
-- Dependencies: 1516 1516
-- Name: perm_id_pk; Type: CONSTRAINT; Schema: system; Owner: -; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT perm_id_pk PRIMARY KEY (permission_id);


--
-- TOC entry 1813 (class 2606 OID 1206399)
-- Dependencies: 1518 1518
-- Name: role_id_pk; Type: CONSTRAINT; Schema: system; Owner: -; Tablespace: 
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT role_id_pk PRIMARY KEY (role_id);


--
-- TOC entry 1815 (class 2606 OID 1206401)
-- Dependencies: 1520 1520
-- Name: user_id_pk; Type: CONSTRAINT; Schema: system; Owner: -; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT user_id_pk PRIMARY KEY (user_id);


--
-- TOC entry 1817 (class 2606 OID 1206403)
-- Dependencies: 1520 1520
-- Name: user_name_uk; Type: CONSTRAINT; Schema: system; Owner: -; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT user_name_uk UNIQUE (user_name);


--
-- TOC entry 1818 (class 2606 OID 1206404)
-- Dependencies: 1512 1814 1520
-- Name: audit_trail_user_id_fk; Type: FK CONSTRAINT; Schema: system; Owner: -
--

ALTER TABLE ONLY audit_trail
    ADD CONSTRAINT audit_trail_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;


--
-- TOC entry 1819 (class 2606 OID 1206409)
-- Dependencies: 1518 1516 1812
-- Name: permissios_role_id_fk; Type: FK CONSTRAINT; Schema: system; Owner: -
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissios_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE;


--
-- TOC entry 1820 (class 2606 OID 1206414)
-- Dependencies: 1518 1812 1520
-- Name: users_role_id_fk; Type: FK CONSTRAINT; Schema: system; Owner: -
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL;


-- Completed on 2012-03-27 07:52:03 GMT

--
-- PostgreSQL database dump complete
--

