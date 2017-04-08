from time import sleep
from random import random

class Node():

    def __init__(self, name, readers=[], writers=[]):
        self.name = name
        self.readers = []
        self.listener = self.listen()
        self.listener.send(None)  # prime to coroutine

    def add_reader(self, reader):
        self.readers.append(reader.listener)

    def listen(self):
        while True:
            name, val = (yield)
            print("{} heard {:.3f} from {}".format(self.name, val, name))

    def decide_to_write(self):
        while True:
            w = random()
            sleep(w)
            self.write(w)

    def write(self, w):
        for reader in self.readers:
            reader.send((self.name, w))

nodes = [Node(x) for x in ('bob', 'darnette', 'eloquise', 'daryll')]
bob2 = Node('bob2')
node = Node('bob')
node.add_reader(bob2)

for _ in node.decide_to_write():
    pass

# w = writer()
# wrap = writer_wrapper(w)
# wrap.send(None)  # prine the coroutine
# for i in range(4):
#     wrap.send(i)
