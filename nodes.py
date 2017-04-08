from time import sleep
from random import random

class Node():

    def __init__(self, name, readers=[], writers=[]):
        self.name = name
        readers = readers if readers is not None else [] 
        self.readers = [reader.listen() for reader in readers]
        # gratuitous comprehension just for the side effect
        _ = [r.send(None) for r in self.readers]  # prime coroutines
        #self.writers = writers if writers is not None else []

    def listen(self):
        while True:
            r = (yield)
            print(r)

    def decide_to_write(self):
        while True:
            w = random()
            sleep(w)
            self.write(w)

    def write(self, w):
        for reader in self.readers:
            reader.send(w)


bob2 = Node('bob2')
node = Node('bob', [bob2])

for _ in node.decide_to_write():
    pass

# w = writer()
# wrap = writer_wrapper(w)
# wrap.send(None)  # prine the coroutine
# for i in range(4):
#     wrap.send(i)
