cmake_minimum_required(VERSION 2.6)

SET(CMAKE_CXX_FLAGS "-std=gnu++11") #Turn on C++11 Support

//set(FILES_TO_TEST Testable.cpp)
//set(UNIT_TESTS Test.cpp)
set(MAIN_FILE main.cpp)

add_subdirectory(gtest) #Build all the gtest stuff
include_directories(gtest/include)
include_directories(.)
add_library(codeToTest ${FILES_TO_TEST})

add_executable(Proj2 ${MAIN_FILE})
target_link_libraries(Proj2 codeToTest)

add_executable(unit-test ${UNIT_TESTS})
target_link_libraries(unit-test gtest gtest_main rt pthread codeToTest)
