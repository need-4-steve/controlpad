package com.controlpad.payman_common.migration;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;

public interface MigrationMapper {

    @Select("SELECT MAX(version) FROM migrations")
    Long findCurrentVersion();

    @Insert("INSERT INTO migrations(version) VALUES(#{0})")
    int insert(Long version);

}
