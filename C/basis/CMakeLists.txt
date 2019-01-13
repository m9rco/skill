cmake_minimum_required(VERSION 3.6)
project(project_name C)

set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -Werror")

set(SOURCE_FILES main.c)
add_executable(main ${SOURCE_FILES})