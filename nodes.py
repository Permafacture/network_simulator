from time import sleep
from random import random

class Node():

    def __init__(self, name, readers=[], writers=[]):
        self.name = name
        self.readers = readers if readers is not None else []
        self.writers = writers if writers is not None else []

    def decide_to_write(self):
        while True:
            w = random()
            sleep(w)
            self.write(w)

    def write(self, w):
        for reader in self.readers:
            reader.send(w)

def reader():
    while True:
        r = (yield)
        print(r)

r = reader()
r.send(None)

node = Node('bob', [r])

for _ in node.decide_to_write():
    pass

# w = writer()
# wrap = writer_wrapper(w)
# wrap.send(None)  # prine the coroutine
# for i in range(4):
#     wrap.send(i)
