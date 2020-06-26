package com.controlpad.payman_common.util;

import org.apache.commons.lang3.StringUtils;

import java.util.ArrayList;
import java.util.List;

public class NameParser {
    private String firstName = "";
    private String lastName = "";
    private String middleName = "";
    private List<String> middleNames = new ArrayList<>();
    private List<String> titlesBefore = new ArrayList<>();
    private List<String> titlesAfter = new ArrayList<>();
    private String[] prefixes = { "dr", "mr", "ms", "atty", "prof", "miss", "mrs" };
    private String[] suffixes = { "jr", "sr", "ii", "iii", "iv", "v", "vi", "esq", "2nd", "3rd", "jd", "phd",
            "md", "cpa" };

    public NameParser() {
    }

    public NameParser(String name) {
        parse(name);
    }

    private void reset() {
        firstName = lastName = middleName = "";
        middleNames = new ArrayList<>();
        titlesBefore = new ArrayList<>();
        titlesAfter = new ArrayList<>();
    }

    private boolean isOneOf(String checkStr, String[] titles) {
        for (String title : titles) {
            if (StringUtils.equalsIgnoreCase(checkStr, title))
                return true;
        }
        return false;
    }

    public void parse(String name) {
        if (StringUtils.isBlank(name))
            return;
        this.reset();
        String[] words = name.split(" ");
        boolean isFirstName = false;

        for (String word : words) {
            if (StringUtils.isBlank(word))
                continue;
            if (word.charAt(word.length() - 1) == '.') {
                if (!isFirstName && !this.isOneOf(word, prefixes)) {
                    firstName = word;
                    isFirstName = true;
                } else if (isFirstName) {
                    middleNames.add(word);
                } else {
                    titlesBefore.add(word);
                }
            } else {
                if (word.endsWith(","))
                    word = StringUtils.chop(word);
                if (isFirstName == false) {
                    firstName = word;
                    isFirstName = true;
                } else {
                    middleNames.add(word);
                }
            }
        }
        if (middleNames.size() > 0) {
            boolean stop = false;
            List<String> toRemove = new ArrayList<>();
            for (int i = middleNames.size() - 1; i >= 0 && !stop; i--) {
                String str = middleNames.get(i);
                if (this.isOneOf(str, suffixes)) {
                    titlesAfter.add(str);
                } else {
                    lastName = str;
                    stop = true;
                }
                toRemove.add(str);
            }
            if (StringUtils.isBlank(lastName) && titlesAfter.size() > 0) {
                lastName = titlesAfter.get(titlesAfter.size() - 1);
                titlesAfter.remove(titlesAfter.size() - 1);
            }
            for (String s : toRemove) {
                middleNames.remove(s);
            }
        }
    }

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public String getMiddleName() {
        if (StringUtils.isBlank(this.middleName)) {
            for (String name : middleNames) {
                middleName += (name + " ");
            }
            middleName = StringUtils.chop(middleName);
        }
        return middleName;
    }

    public List<String> getTitlesBefore() {
        return titlesBefore;
    }

    public List<String> getTitlesAfter() {
        return titlesAfter;
    }

}