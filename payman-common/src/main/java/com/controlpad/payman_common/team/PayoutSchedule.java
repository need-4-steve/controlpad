package com.controlpad.payman_common.team;


import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PayoutScheduleValidate;
import org.apache.commons.lang3.StringUtils;
import org.hibernate.validator.constraints.NotBlank;
import org.hibernate.validator.constraints.NotEmpty;
import org.joda.time.DateTime;

import javax.validation.Valid;
import javax.validation.constraints.Max;
import javax.validation.constraints.Min;
import javax.validation.constraints.NotNull;
import java.util.List;
import java.util.Locale;


@PayoutScheduleValidate(groups = AlwaysCheck.class)
public class PayoutSchedule {

    @NotEmpty(message = "days cannot be empty", groups = AlwaysCheck.class)
    @Valid
    private List<Integer> days;
    @NotBlank(message = "typeOfDay required", groups = AlwaysCheck.class)
    private String typeOfDay;
    @Min(value = 0, message = "hourOfDay cannot be negative", groups = AlwaysCheck.class)
    @Max(value = 23, message = "hourOfDay cannot be greater than 23", groups = AlwaysCheck.class)
    private Integer hourOfDay;
    @NotNull(message = "payoutSchedule.days - daysBuffer required", groups = AlwaysCheck.class)
    @Min(value = 0, message = "daysBuffer cannot be negative")
    private Integer daysBuffer;
    @NotNull(message = "payoutSchedule.days - bufferHourOfDay required", groups = AlwaysCheck.class)
    @Min(value = 0, message = "bufferHourOfDay cannot be negative")
    @Max(value = 23, message = "bufferHourOfDay cannot be greater than 23")
    private Integer bufferHourOfDay;

    public PayoutSchedule() {
    }

    public PayoutSchedule(List<Integer> days, String typeOfDay, Integer hourOfDay, Integer daysBuffer, Integer bufferHourOfDay) {
        this.days = days;
        this.typeOfDay = typeOfDay;
        this.hourOfDay = hourOfDay;
        this.daysBuffer = daysBuffer;
        this.bufferHourOfDay = bufferHourOfDay;
    }

    public List<Integer> getDays() {
        return days;
    }

    public void setDays(List<Integer> days) {
        this.days = days;
    }

    public String getTypeOfDay() {
        return typeOfDay;
    }

    public Integer getHourOfDay() {
        return hourOfDay;
    }

    public Integer getDaysBuffer() {
        return daysBuffer;
    }

    public Integer getBufferHourOfDay() {
        return bufferHourOfDay;
    }

    public void setHourOfDay(Integer hourOfDay) {
        this.hourOfDay = hourOfDay;
    }

    public void setDaysBuffer(Integer daysBuffer) {
        this.daysBuffer = daysBuffer;
    }

    public void setBufferHourOfDay(Integer bufferHourOfDay) {
        this.bufferHourOfDay = bufferHourOfDay;
    }

    public String getCron() {
        String daysString = "";
        String hourString;
        String weekDaysString = "";
        DayType type = DayType.getForName(typeOfDay);
        switch (type) {
            case MONTH:
                daysString = StringUtils.join(days, ",");
                break;
            case WEEK:
                weekDaysString = StringUtils.join(days, ",");
                break;
            default:
                return null;
        }
        if (hourOfDay != null) {
            hourString = String.valueOf(hourOfDay);
        } else {
            hourString = "0";
        }
        if (StringUtils.isBlank(daysString)) {
            daysString = "?";
        }
        if (StringUtils.isBlank(weekDaysString)) {
            weekDaysString = "?";
        }
        return String.format(Locale.US, "0 0 %s %s * %s", hourString, daysString, weekDaysString);
    }

    public String getDailyCron() {
        String hourString;
        if (hourOfDay != null) {
            hourString = String.valueOf(hourOfDay);
        } else {
            hourString = "0";
        }
        return String.format(Locale.US, "0 0 %s * * 1-5", hourString);
    }

    public void setTypeOfDay(String typeOfDay) {
        this.typeOfDay = typeOfDay;
    }

    public boolean isSameDay(DateTime date) {
        DayType type = DayType.getForName(typeOfDay);
        switch (type) {
            case MONTH:
                return days.contains(date.getDayOfMonth());
            case WEEK:
                return days.contains(date.getDayOfWeek());
        }
        return false;
    }

    public enum DayType {
        MONTH,
        WEEK,
        UNKNOWN;

        public static DayType getForName(String name) {
            for (DayType dayType: DayType.values()) {
                if (StringUtils.equalsIgnoreCase(name, dayType.name())) {
                    return dayType;
                }
            }
            return UNKNOWN;
        }
    }
}