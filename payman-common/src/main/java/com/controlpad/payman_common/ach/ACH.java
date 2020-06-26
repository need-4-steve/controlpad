/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.ach;

import com.controlpad.payman_common.validation.AlwaysCheck;
import org.apache.commons.lang3.math.NumberUtils;
import org.hibernate.validator.constraints.NotBlank;


@ACHFieldsValidate(groups = AlwaysCheck.class)
public class ACH {

    private Long id;
    @NotBlank(message = "destinationRoute required", groups = AlwaysCheck.class)
    private String destinationRoute;
    @NotBlank(message = "originRoute required", groups = AlwaysCheck.class)
    private String originRoute;
    @NotBlank(message = "destinationName required", groups = AlwaysCheck.class)
    private String destinationName;
    @NotBlank(message = "originName required", groups = AlwaysCheck.class)
    private String originName;
    @NotBlank(message = "companyName required", groups = AlwaysCheck.class)
    private String companyName;
    @NotBlank(message = "companyId required", groups = AlwaysCheck.class)
    private String companyId;

    public ACH() {

    }

    public ACH(Long id, String destinationRoute, String originRoute, String destinationName, String originName, String companyName, String companyId) {
        this.id = id;
        this.destinationRoute = destinationRoute;
        this.originRoute = originRoute;
        this.destinationName = destinationName;
        this.originName = originName;
        this.companyName = companyName;
        this.companyId = companyId;
    }

    public Long getId() {
        return id;
    }

    public String getOriginRoute() {
        return originRoute;
    }

    public String getDestinationRoute() {
        return destinationRoute;
    }

    public String getDestinationName() {
        return destinationName;
    }

    public String getOriginName() {
        return originName;
    }

    public String getCompanyId() {
        return companyId;
    }

    public String getCompanyName() {
        return companyName;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public long getODFI() {
        // Returns the first 8 of the routing number.
        // Last number is used for a checksum to verify the number is valid
        // Seems origin route can be a company id instead of a routing number, so only using destination route for now
        return NumberUtils.createLong(destinationRoute) / 10;
    }
}
