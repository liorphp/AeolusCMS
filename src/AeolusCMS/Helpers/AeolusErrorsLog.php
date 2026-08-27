<?php
namespace AeolusCMS\Helpers;


/**
 * ============================================================
 * לוג שגיאות
 * ============================================================
 *
 * המתודות כאן היו ריקות, וכל קריאה אליהן מכל המערכת נזרקה לפח.
 * המשמעות המעשית: כל תקלה שקטה נראתה בדיוק כמו פיצ'ר תקין, ולא
 * הייתה שום דרך לדעת שמשהו לא עובד עד שלקוח התלונן.
 *
 * המימוש כאן מינימלי בכוונה. הוא כותב ליעד ש-error_log מוגדר
 * אליו ב-php.ini, ולכן הוא עובד גם ב-CLI וגם ב-web בלי תלות
 * בשום תשתית נוספת.
 */
class AeolusErrorsLog {

    /** קידומת אחידה, כדי שאפשר יהיה לסנן בלוג */
    const PREFIX = '[AEOLUS] ';

    /** חיתוך של פרמטרים ארוכים, כדי לא להציף את הלוג */
    const MAX_PARAMS_LEN = 4000;


    public static function set() {
        // שמור לתאימות עם קריאות קיימות
    }


    /**
     * הודעה עם הקשר.
     *
     * $params נכתב כ-JSON כדי שערכים מקוננים, כמו מערך השגיאות
     * שקלאודפלייר מחזירה, יישמרו קריאים ולא יהפכו ל-"Array".
     */
    public static function sendMessage($message, $params = array()) {
        $line = self::PREFIX . (string)$message;

        if (!empty($params)) {
            $json = \json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // json_encode נכשל על בתים שאינם UTF-8 תקין. עדיף
            // הודעה בלי הקשר מאשר שורה ריקה בלוג.
            if ($json === false) {
                $json = '{"encode_error":"' . \json_last_error_msg() . '"}';
            }

            if (\strlen($json) > self::MAX_PARAMS_LEN) {
                $json = \substr($json, 0, self::MAX_PARAMS_LEN) . '...[truncated]';
            }

            $line .= ' ' . $json;
        }

        \error_log($line);
    }


    /**
     * חריגה, עם המקום שבה נזרקה.
     *
     * ה-stack trace נכתב בשורה נפרדת כדי שהשורה הראשונה תישאר
     * קריאה ב-grep.
     */
    public static function sendException($exception, $params = null) {
        if (!($exception instanceof \Throwable)) {
            self::sendMessage('non-throwable passed to sendException', array(
                'type' => \gettype($exception),
            ));
            return;
        }

        $context = \is_array($params) ? $params : array();

        $context['class'] = \get_class($exception);
        $context['where'] = $exception->getFile() . ':' . $exception->getLine();

        self::sendMessage($exception->getMessage(), $context);

        \error_log(self::PREFIX . 'trace: ' . $exception->getTraceAsString());
    }
}