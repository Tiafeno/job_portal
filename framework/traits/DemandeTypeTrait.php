<?php
namespace JP\Framework\Traits;

trait DemandeTypeTrait {
    public static $tableName;
    public static function getTableName() {
        global $wpdb;
        return self::$tableName = $wpdb->prefix . 'demande_type';
    }

    /**
     * @param string|null $name
     * @return int
     */
    public static function getTypeId(?string $name): int {
        global $wpdb;
        $table = self::getTableName();
        $sql = "SELECT ID FROM $table WHERE name = %s";
        $prepare = $wpdb->prepare($sql, $name);
        $result = $wpdb->get_var($prepare);
        $wpdb->flush();
        return null === $result ? 0 : intval($result);
    }

    public static function getTypeName(int $id): ?string {
        global $wpdb;
        $table = self::getTableName();
        $prepare = $wpdb->prepare("SELECT name FROM $table WHERE ID = %d", $id);
        $name = $wpdb->get_var($prepare);
        $wpdb->flush();
        return null === $name ? null : $name;
    }

    public static function getDemandeType(int $id) {
        global $wpdb;
        $table = self::getTableName();
        $prepare = $wpdb->prepare("SELECT * FROM $table WHERE ID = %d", $id);
        $demandeType = $wpdb->get_row($prepare);
        $wpdb->flush();
        return  $demandeType;
    }

}