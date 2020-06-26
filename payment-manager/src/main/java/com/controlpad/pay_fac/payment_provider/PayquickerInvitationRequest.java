package com.controlpad.pay_fac.payment_provider;

import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

public class PayquickerInvitationRequest {

    @NotBlank(message = "userId required", groups = AlwaysCheck.class)
    private String userId;
    @NotBlank(message = "teamId required", groups = AlwaysCheck.class)
    private String teamId;
    @NotBlank(message = "firstName required", groups = AlwaysCheck.class)
    private String firstName;
    @NotBlank(message = "lastName required", groups = AlwaysCheck.class)
    private String lastName;
    @NotBlank(message = "email required", groups = AlwaysCheck.class)
    private String email;

    public String getUserId() {
        return userId;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public String getEmail() {
        return email;
    }

}
