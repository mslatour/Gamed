import random, math

def createBinary():
  binary = range(11)
  for i in range(11):
    binary[i] = int(round((random.random())))
  return binary

def freq():
  binary = [0 for x in range(11)]
  for i in range(20):
    bin2 = createBinary()
    binary = [binary[x] + bin2[x] for x in range(11)]
  return binary

print freq()

  

