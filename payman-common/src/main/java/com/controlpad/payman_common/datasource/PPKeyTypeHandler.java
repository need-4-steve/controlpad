package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.payment_provider.PaymentProviderCredentials;
import com.controlpad.payman_common.util.EncryptUtil;
import com.controlpad.payman_common.util.GsonUtil;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;

import java.nio.charset.Charset;
import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

// Used for payment provider table
public class PPKeyTypeHandler extends BaseTypeHandler<PaymentProviderCredentials> {

    private static final byte[] key = "on0*3ncXg^lZp2cy".getBytes(Charset.forName("UTF8"));

    private EncryptUtil encryptUtil;

    public PPKeyTypeHandler() {
        encryptUtil = EncryptUtil.getInstance();
    }

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, PaymentProviderCredentials config, JdbcType jdbcType) throws SQLException {
        ps.setBytes(i, encryptUtil.encryptString(key, GsonUtil.getGson().toJson(config)));
    }

    @Override
    public PaymentProviderCredentials getNullableResult(ResultSet rs, String columnName) throws SQLException {
        byte[] data = rs.getBytes(columnName);
        if (data == null)
            return null;
        return GsonUtil.getGson().fromJson(encryptUtil.decryptString(key, data), PaymentProviderCredentials.class);
    }

    @Override
    public PaymentProviderCredentials getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        byte[] data = rs.getBytes(columnIndex);
        if (data == null)
            return null;
        return GsonUtil.getGson().fromJson(encryptUtil.decryptString(key, data), PaymentProviderCredentials.class);
    }

    @Override
    public PaymentProviderCredentials getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return null;
    }
}