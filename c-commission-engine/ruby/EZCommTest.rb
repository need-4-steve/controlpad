require 'ffi'

module FfiEzComm
  extend FFI::Library
  ffi_lib 'c'
  ffi_lib './EzComm.so'
  #attach_function :_Z7Startupv, [], :int
  #attach_function :_Z6SetVarPKcS0_, [:string, :string], :int
  #attach_function :_Z7Processv, [], :string
end


puts FfiEzComm._Z7Startupv()
puts FfiEzComm._Z6SetVarPKcS0_("test1", "test2")
puts FfiEzComm._Z7Processv()