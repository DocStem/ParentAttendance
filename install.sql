
/**********************************************************************
 install.sql file
 Required if the module adds programs to other modules
***********************************************************************/

-- Fix #102 error language "plpgsql" does not exist
-- http://timmurphy.org/2011/08/27/create-language-if-it-doesnt-exist-in-postgresql/
--
-- Name: create_language_plpgsql(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION create_language_plpgsql()
RETURNS BOOLEAN AS $$
    CREATE LANGUAGE plpgsql;
    SELECT TRUE;
$$ LANGUAGE SQL;

SELECT CASE WHEN NOT (
    SELECT TRUE AS exists FROM pg_language
    WHERE lanname='plpgsql'
    UNION
    SELECT FALSE AS exists
    ORDER BY exists DESC
    LIMIT 1
) THEN
    create_language_plpgsql()
ELSE
    FALSE
END AS plpgsql_created;

DROP FUNCTION create_language_plpgsql();


/*******************************************************
 profile_id:
    - 0: student
    - 1: admin
    - 2: teacher
    - 3: parent
 modname: should match the Menu.php entries
 can_use: 'Y'
 can_edit: 'Y' or null (generally null for non admins)
*******************************************************/
--
-- Data for Name: profile_exceptions; Type: TABLE DATA;
--


-- Add For Parents
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'ParentAttendance/ParentAttendance.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='ParentAttendance/ParentAttendance.php'
    AND profile_id=3);

-- Add for Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'ParentAttendance/ParentAttendance.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='ParentAttendance/ParentAttendance.php'
    AND profile_id=1);

--  allows admin to create the REASON codes for Parents
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'ParentAttendance/AbsentReasonCodes.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='ParentAttendance/AbsentReasonCodes.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'ParentAttendance/SetupOptions.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='ParentAttendance/SetupOptions.php'
    AND profile_id=1);

/**
 * program_config Table
 *
 * syear: school year (school may have various years in DB)
 * school_id: may exists various schools in DB
 * program: convention is plugin name, for ex.: 'student_billing_premium'
 * title: for ex.: 'STUDENT_PAYMENT_RECEIPTS_[your_program_config]'
 * value: string
 */
--
-- Data for Name: program_config; Type: TABLE DATA; Schema: public; Owner: rosariosis
--



/**
 * Add module tables
 */

 -- Table: public.parentattendance_reasoncodes

-- DROP TABLE public.parentattendance_reasoncodes;
-- SEQUENCE: public.attendance_codes_id_seq

-- DROP SEQUENCE public.attendance_codes_id_seq;

CREATE SEQUENCE IF NOT EXISTS  public.parentattendance_reasoncodes_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 2147483647
    CACHE 1;

ALTER SEQUENCE public.parentattendance_reasoncodes_id_seq
    OWNER TO rosariosis;
    
-- Table: public.parentattendance_reasoncodes

-- DROP TABLE public.parentattendance_reasoncodes;

CREATE TABLE IF NOT EXISTS public.parentattendance_reasoncodes
(
    id integer NOT NULL DEFAULT nextval('parentattendance_reasoncodes_id_seq'::regclass),
    school_id integer NOT NULL,
    title text COLLATE pg_catalog."default" NOT NULL,
    short_name character varying(10) COLLATE pg_catalog."default",
    type character varying(20) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    CONSTRAINT parentattendance_reasoncodes_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.parentattendance_reasoncodes
    OWNER to rosariosis;
-- Index: parentattendance_reasoncodes_ind3

-- DROP INDEX public.parentattendance_reasoncodes_ind3;

CREATE INDEX parentattendance_reasoncodes_ind3
    ON public.parentattendance_reasoncodes USING btree
    (short_name COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: parentattendance_selfreported_ind3

-- DROP INDEX public.parentattendance_selfreported_ind3;

CREATE INDEX parentattendance_selfreported_ind3
    ON public.parentattendance_reasoncodes USING btree
    (short_name COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;

-- Trigger: set_updated_at

-- DROP TRIGGER set_updated_at ON public.parentattendance_reasoncodes;

CREATE TRIGGER set_updated_at
    BEFORE UPDATE 
    ON public.parentattendance_reasoncodes
    FOR EACH ROW
    EXECUTE PROCEDURE public.set_updated_at();



-- Table: public.attendance_codes

-- DROP TABLE public.attendance_codes;

-- DROP SEQUENCE public.attendance_codes_id_seq;

CREATE SEQUENCE IF NOT EXISTS public.parentattendance_setupoptions_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 2147483647
    CACHE 1;

ALTER SEQUENCE public.parentattendance_setupoptions_id_seq
    OWNER TO rosariosis;

-- Table: public.parentattendance_setupoptions

-- DROP TABLE public.parentattendance_setupoptions;

CREATE TABLE IF NOT EXISTS public.parentattendance_setupoptions
(
    id integer NOT NULL DEFAULT nextval('parentattendance_setupoptions_id_seq'::regclass),
    school_id integer NOT NULL,
    syear integer NOT NULL,
    cutoff_time character varying(4) COLLATE pg_catalog."default" NOT NULL,
    parentabsence_codes_permited character varying(80) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    CONSTRAINT parentattendance_setupoptions_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.parentattendance_setupoptions
    OWNER to rosariosis;



    -- Table: public.parentattendance_reasoncodes

CREATE SEQUENCE public.parentattendance_selfreported_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 2147483647
    CACHE 1;

ALTER SEQUENCE public.parentattendance_selfreported_id_seq
    OWNER TO rosariosis;
-- DROP TABLE public.parentattendance_reasoncodes;

-- Table: public.parentattendance_selfreported

-- DROP TABLE public.parentattendance_selfreported;

CREATE TABLE IF NOT EXISTS public.parentattendance_selfreported
(
    id integer NOT NULL DEFAULT nextval('parentattendance_selfreported_id_seq'::regclass),
    school_id integer NOT NULL,
    syear integer NOT NULL,
    student_id integer NOT NULL,
    school_date date NOT NULL,
    absent_type integer NOT NULL,
    absent_reason character varying(50) COLLATE pg_catalog."default" NOT NULL,
    absence_note text COLLATE pg_catalog."default" NOT NULL,
    parent_reporting integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    CONSTRAINT parentattendance_selfreported_pkey PRIMARY KEY (id),
    CONSTRAINT parentattendance_selfreported_school_id_syear_student_id_sc_key UNIQUE (school_id, syear, student_id, school_date)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.parentattendance_selfreported
    OWNER to rosariosis;

-- Trigger: set_updated_at

-- DROP TRIGGER set_updated_at ON public.parentattendance_selfreported;

CREATE TRIGGER set_updated_at
    BEFORE UPDATE 
    ON public.parentattendance_selfreported
    FOR EACH ROW
    EXECUTE PROCEDURE public.set_updated_at();