package com.controlpad.payman_common.serialization;


import com.google.gson.TypeAdapter;
import com.google.gson.stream.JsonReader;
import com.google.gson.stream.JsonWriter;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;

import java.io.IOException;

public class DateTimeGsonAdapter extends TypeAdapter<DateTime> {

    DateTimeFormatter dateTimeFormatter;

    public DateTimeGsonAdapter() {
        dateTimeFormatter = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss");
    }

    @Override
    public void write(JsonWriter jsonWriter, DateTime dateTime) throws IOException {
        if (dateTime == null) {
            jsonWriter.nullValue();
            return;
        }
        jsonWriter.value(dateTime.toString(dateTimeFormatter));
    }

    @Override
    public DateTime read(JsonReader jsonReader) throws IOException {
        return dateTimeFormatter.parseDateTime(jsonReader.nextString());
    }
}