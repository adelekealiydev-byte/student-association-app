<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION id_cards_before_write() RETURNS trigger AS $$
            DECLARE pay payments%ROWTYPE;
            BEGIN
                SELECT * INTO pay FROM payments WHERE payment_id = NEW.payment_id;
                IF pay.member_id IS DISTINCT FROM NEW.member_id THEN
                    RAISE EXCEPTION 'Card member % does not own payment %', NEW.member_id, NEW.payment_id;
                END IF;
                IF pay.payment_type <> 'Dues' THEN
                    RAISE EXCEPTION 'An ID card must link to a Dues payment';
                END IF;
                NEW.is_eligible := pay.status = 'Completed'
                    AND EXISTS (SELECT 1 FROM members m WHERE m.member_id = NEW.member_id AND m.is_verified);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER id_cards_before_write
                BEFORE INSERT OR UPDATE ON id_cards
                FOR EACH ROW EXECUTE FUNCTION id_cards_before_write();

            CREATE OR REPLACE FUNCTION touch_id_cards() RETURNS trigger AS $$
            BEGIN
                IF TG_TABLE_NAME = 'payments' THEN
                    UPDATE id_cards SET is_eligible = is_eligible WHERE payment_id = NEW.payment_id;
                ELSE
                    UPDATE id_cards SET is_eligible = is_eligible WHERE member_id = NEW.member_id;
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER payments_touch_cards
                AFTER UPDATE OF status, payment_type ON payments
                FOR EACH ROW EXECUTE FUNCTION touch_id_cards();

            CREATE TRIGGER members_touch_cards
                AFTER UPDATE OF is_verified ON members
                FOR EACH ROW EXECUTE FUNCTION touch_id_cards();
            SQL);
        } elseif (DB::getDriverName() === 'mysql') {
            DB::unprepared(<<<'SQL'
            CREATE TRIGGER id_cards_before_insert
            BEFORE INSERT ON id_cards
            FOR EACH ROW
            BEGIN
                DECLARE v_pay_member_id BIGINT UNSIGNED;
                DECLARE v_pay_type VARCHAR(50);
                DECLARE v_pay_status VARCHAR(50);
                DECLARE v_is_verified TINYINT(1) DEFAULT 0;

                SELECT member_id, payment_type, status INTO v_pay_member_id, v_pay_type, v_pay_status
                FROM payments WHERE payment_id = NEW.payment_id;

                IF v_pay_member_id IS NULL OR v_pay_member_id != NEW.member_id THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Card member does not own payment';
                END IF;

                IF v_pay_type != 'Dues' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'An ID card must link to a Dues payment';
                END IF;

                SELECT is_verified INTO v_is_verified FROM members WHERE member_id = NEW.member_id;

                IF v_pay_status = 'Completed' AND v_is_verified = 1 THEN
                    SET NEW.is_eligible = 1;
                ELSE
                    SET NEW.is_eligible = 0;
                END IF;
            END;
            SQL);

            DB::unprepared(<<<'SQL'
            CREATE TRIGGER id_cards_before_update
            BEFORE UPDATE ON id_cards
            FOR EACH ROW
            BEGIN
                DECLARE v_pay_member_id BIGINT UNSIGNED;
                DECLARE v_pay_type VARCHAR(50);
                DECLARE v_pay_status VARCHAR(50);
                DECLARE v_is_verified TINYINT(1) DEFAULT 0;

                SELECT member_id, payment_type, status INTO v_pay_member_id, v_pay_type, v_pay_status
                FROM payments WHERE payment_id = NEW.payment_id;

                IF v_pay_member_id IS NULL OR v_pay_member_id != NEW.member_id THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Card member does not own payment';
                END IF;

                IF v_pay_type != 'Dues' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'An ID card must link to a Dues payment';
                END IF;

                SELECT is_verified INTO v_is_verified FROM members WHERE member_id = NEW.member_id;

                IF v_pay_status = 'Completed' AND v_is_verified = 1 THEN
                    SET NEW.is_eligible = 1;
                ELSE
                    SET NEW.is_eligible = 0;
                END IF;
            END;
            SQL);

            DB::unprepared(<<<'SQL'
            CREATE TRIGGER payments_after_update
            AFTER UPDATE ON payments
            FOR EACH ROW
            BEGIN
                IF OLD.status != NEW.status OR OLD.payment_type != NEW.payment_type THEN
                    UPDATE id_cards SET updated_at = NOW() WHERE payment_id = NEW.payment_id;
                END IF;
            END;
            SQL);

            DB::unprepared(<<<'SQL'
            CREATE TRIGGER members_after_update
            AFTER UPDATE ON members
            FOR EACH ROW
            BEGIN
                IF OLD.is_verified != NEW.is_verified THEN
                    UPDATE id_cards SET updated_at = NOW() WHERE member_id = NEW.member_id;
                END IF;
            END;
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS members_touch_cards ON members;
            DROP TRIGGER IF EXISTS payments_touch_cards ON payments;
            DROP TRIGGER IF EXISTS id_cards_before_write ON id_cards;
            DROP FUNCTION IF EXISTS touch_id_cards();
            DROP FUNCTION IF EXISTS id_cards_before_write();
            SQL);
        } elseif (DB::getDriverName() === 'mysql') {
            DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS members_after_update;
            DROP TRIGGER IF EXISTS payments_after_update;
            DROP TRIGGER IF EXISTS id_cards_before_update;
            DROP TRIGGER IF EXISTS id_cards_before_insert;
            SQL);
        }
    }
};

