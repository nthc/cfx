--
-- PostgreSQL database dump
--

-- Started on 2012-03-26 19:48:44 GMT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 6 (class 2615 OID 1198248)
-- Name: system; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA system;


ALTER SCHEMA system OWNER TO postgres;

SET search_path = system, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1514 (class 1259 OID 1206315)
-- Dependencies: 6
-- Name: audit_trail; Type: TABLE; Schema: system; Owner: postgres; Tablespace: 
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


ALTER TABLE system.audit_trail OWNER TO postgres;

--
-- TOC entry 1513 (class 1259 OID 1206313)
-- Dependencies: 6 1514
-- Name: audit_trail_audit_trail_id_seq; Type: SEQUENCE; Schema: system; Owner: postgres
--

CREATE SEQUENCE audit_trail_audit_trail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE system.audit_trail_audit_trail_id_seq OWNER TO postgres;

--
-- TOC entry 1816 (class 0 OID 0)
-- Dependencies: 1513
-- Name: audit_trail_audit_trail_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: postgres
--

ALTER SEQUENCE audit_trail_audit_trail_id_seq OWNED BY audit_trail.audit_trail_id;


--
-- TOC entry 1516 (class 1259 OID 1206331)
-- Dependencies: 6
-- Name: audit_trail_data; Type: TABLE; Schema: system; Owner: postgres; Tablespace: 
--

CREATE TABLE audit_trail_data (
    audit_trail_data_id integer NOT NULL,
    audit_trail_id integer NOT NULL,
    data text
);


ALTER TABLE system.audit_trail_data OWNER TO postgres;

--
-- TOC entry 1515 (class 1259 OID 1206329)
-- Dependencies: 1516 6
-- Name: audit_trail_data_audit_trail_data_id_seq; Type: SEQUENCE; Schema: system; Owner: postgres
--

CREATE SEQUENCE audit_trail_data_audit_trail_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE system.audit_trail_data_audit_trail_data_id_seq OWNER TO postgres;

--
-- TOC entry 1817 (class 0 OID 0)
-- Dependencies: 1515
-- Name: audit_trail_data_audit_trail_data_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: postgres
--

ALTER SEQUENCE audit_trail_data_audit_trail_data_id_seq OWNED BY audit_trail_data.audit_trail_data_id;


--
-- TOC entry 1508 (class 1259 OID 1198252)
-- Dependencies: 6
-- Name: permissions; Type: TABLE; Schema: system; Owner: postgres; Tablespace: 
--

CREATE TABLE permissions (
    permission_id integer NOT NULL,
    role_id integer NOT NULL,
    permission character varying(4000),
    value numeric NOT NULL,
    module character varying(4000)
);


ALTER TABLE system.permissions OWNER TO postgres;

--
-- TOC entry 1509 (class 1259 OID 1198258)
-- Dependencies: 1508 6
-- Name: permissions_permission_id_seq; Type: SEQUENCE; Schema: system; Owner: postgres
--

CREATE SEQUENCE permissions_permission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE system.permissions_permission_id_seq OWNER TO postgres;

--
-- TOC entry 1818 (class 0 OID 0)
-- Dependencies: 1509
-- Name: permissions_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: postgres
--

ALTER SEQUENCE permissions_permission_id_seq OWNED BY permissions.permission_id;


--
-- TOC entry 1510 (class 1259 OID 1198260)
-- Dependencies: 6
-- Name: roles; Type: TABLE; Schema: system; Owner: postgres; Tablespace: 
--

CREATE TABLE roles (
    role_id integer NOT NULL,
    role_name character varying(64)
);


ALTER TABLE system.roles OWNER TO postgres;

--
-- TOC entry 1511 (class 1259 OID 1198263)
-- Dependencies: 1510 6
-- Name: roles_role_id_seq; Type: SEQUENCE; Schema: system; Owner: postgres
--

CREATE SEQUENCE roles_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE system.roles_role_id_seq OWNER TO postgres;

--
-- TOC entry 1819 (class 0 OID 0)
-- Dependencies: 1511
-- Name: roles_role_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: postgres
--

ALTER SEQUENCE roles_role_id_seq OWNED BY roles.role_id;


--
-- TOC entry 1507 (class 1259 OID 1198249)
-- Dependencies: 6
-- Name: users; Type: TABLE; Schema: system; Owner: postgres; Tablespace: 
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


ALTER TABLE system.users OWNER TO postgres;

--
-- TOC entry 1512 (class 1259 OID 1198265)
-- Dependencies: 1507 6
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: system; Owner: postgres
--

CREATE SEQUENCE users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE system.users_user_id_seq OWNER TO postgres;

--
-- TOC entry 1820 (class 0 OID 0)
-- Dependencies: 1512
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: system; Owner: postgres
--

ALTER SEQUENCE users_user_id_seq OWNED BY users.user_id;


--
-- TOC entry 1797 (class 2604 OID 1206318)
-- Dependencies: 1513 1514 1514
-- Name: audit_trail_id; Type: DEFAULT; Schema: system; Owner: postgres
--

ALTER TABLE audit_trail ALTER COLUMN audit_trail_id SET DEFAULT nextval('audit_trail_audit_trail_id_seq'::regclass);


--
-- TOC entry 1798 (class 2604 OID 1206334)
-- Dependencies: 1516 1515 1516
-- Name: audit_trail_data_id; Type: DEFAULT; Schema: system; Owner: postgres
--

ALTER TABLE audit_trail_data ALTER COLUMN audit_trail_data_id SET DEFAULT nextval('audit_trail_data_audit_trail_data_id_seq'::regclass);


--
-- TOC entry 1795 (class 2604 OID 1198267)
-- Dependencies: 1509 1508
-- Name: permission_id; Type: DEFAULT; Schema: system; Owner: postgres
--

ALTER TABLE permissions ALTER COLUMN permission_id SET DEFAULT nextval('permissions_permission_id_seq'::regclass);


--
-- TOC entry 1796 (class 2604 OID 1198268)
-- Dependencies: 1511 1510
-- Name: role_id; Type: DEFAULT; Schema: system; Owner: postgres
--

ALTER TABLE roles ALTER COLUMN role_id SET DEFAULT nextval('roles_role_id_seq'::regclass);


--
-- TOC entry 1794 (class 2604 OID 1198269)
-- Dependencies: 1512 1507
-- Name: user_id; Type: DEFAULT; Schema: system; Owner: postgres
--

ALTER TABLE users ALTER COLUMN user_id SET DEFAULT nextval('users_user_id_seq'::regclass);


--
-- TOC entry 1808 (class 2606 OID 1206323)
-- Dependencies: 1514 1514
-- Name: audit_trail_audit_id_pk; Type: CONSTRAINT; Schema: system; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY audit_trail
    ADD CONSTRAINT audit_trail_audit_id_pk PRIMARY KEY (audit_trail_id);


--
-- TOC entry 1810 (class 2606 OID 1206339)
-- Dependencies: 1516 1516
-- Name: audit_trail_data_id_pk; Type: CONSTRAINT; Schema: system; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY audit_trail_data
    ADD CONSTRAINT audit_trail_data_id_pk PRIMARY KEY (audit_trail_data_id);


--
-- TOC entry 1804 (class 2606 OID 1198271)
-- Dependencies: 1508 1508
-- Name: perm_id_pk; Type: CONSTRAINT; Schema: system; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT perm_id_pk PRIMARY KEY (permission_id);


--
-- TOC entry 1806 (class 2606 OID 1198273)
-- Dependencies: 1510 1510
-- Name: role_id_pk; Type: CONSTRAINT; Schema: system; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT role_id_pk PRIMARY KEY (role_id);


--
-- TOC entry 1800 (class 2606 OID 1198275)
-- Dependencies: 1507 1507
-- Name: user_id_pk; Type: CONSTRAINT; Schema: system; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT user_id_pk PRIMARY KEY (user_id);


--
-- TOC entry 1802 (class 2606 OID 1198277)
-- Dependencies: 1507 1507
-- Name: user_name_uk; Type: CONSTRAINT; Schema: system; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT user_name_uk UNIQUE (user_name);


--
-- TOC entry 1813 (class 2606 OID 1206324)
-- Dependencies: 1799 1514 1507
-- Name: audit_trail_user_id_fk; Type: FK CONSTRAINT; Schema: system; Owner: postgres
--

ALTER TABLE ONLY audit_trail
    ADD CONSTRAINT audit_trail_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;


--
-- TOC entry 1812 (class 2606 OID 1198278)
-- Dependencies: 1805 1510 1508
-- Name: permissios_role_id_fk; Type: FK CONSTRAINT; Schema: system; Owner: postgres
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissios_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE;


--
-- TOC entry 1811 (class 2606 OID 1198283)
-- Dependencies: 1510 1507 1805
-- Name: users_role_id_fk; Type: FK CONSTRAINT; Schema: system; Owner: postgres
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL;


-- Completed on 2012-03-26 19:48:44 GMT

--
-- PostgreSQL database dump complete
--

